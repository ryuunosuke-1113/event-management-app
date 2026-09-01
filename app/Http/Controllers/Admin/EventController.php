<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Throwable;
class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('event_date')->get();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'place' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published,closed,finished,cancelled'],
            'chat_url' => ['nullable', 'url'],
            'cancel_policy' => ['nullable', 'string'],

        ]);
        $validated['organizer_id'] = $request->user()->id;

        $event = Event::create($validated);

        $conversation = $event->conversations()->firstOrCreate([
            'type' => 'event',
        ]);

        $conversation->members()->firstOrCreate([
            'user_id' => $event->organizer_id,
        ]);
        return redirect()
            ->route('admin.events.index')
            ->with('success', 'イベントを作成しました。');
    }
    public function show(Event $event)
    {
        $event->load([
            'participants.user',
            'participants.payment',
        ]);

        return view('admin.events.show', compact('event'));
    }
    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'place' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published,closed,finished,cancelled'],
            'chat_url' => ['nullable', 'url'],
            'cancel_policy' => ['nullable', 'string'],
        ]);

        // 一度中止したイベントを再公開しない
        if (
            $event->status === 'cancelled'
            && $validated['status'] !== 'cancelled'
        ) {
            return back()
                ->withInput()
                ->with('error', '中止済みのイベントを再公開することはできません。');
        }

        $isBeingCancelled =
            $event->status !== 'cancelled'
            && $validated['status'] === 'cancelled';

        if ($isBeingCancelled) {
            $event->load('participants.payment');

            $stripe = new StripeClient(
                config('services.stripe.secret')
            );

            foreach ($event->participants as $participant) {
                $payment = $participant->payment;

                /*
                |--------------------------------------------------------------------------
                | 参加確定 + 支払い済み
                |--------------------------------------------------------------------------
                */
                if (
                    $participant->status === 'confirmed'
                    && $payment
                    && $payment->status === 'paid'
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | Stripe決済
                    |--------------------------------------------------------------------------
                    */
                    if ($payment->payment_method === 'stripe') {
                        if (!$payment->stripe_payment_intent_id) {
                            return back()
                                ->withInput()
                                ->with(
                                    'error',
                                    'Stripeの決済情報が見つからない参加者がいるため、イベント中止を完了できませんでした。'
                                );
                        }

                        try {
                            $stripe->refunds->create([
                                'payment_intent' => $payment->stripe_payment_intent_id,
                            ]);
                        } catch (Throwable $e) {
                            report($e);

                            return back()
                                ->withInput()
                                ->with(
                                    'error',
                                    'Stripeの返金処理に失敗しました。イベントはまだ中止されていません。'
                                );
                        }

                        $payment->update([
                            'status' => 'refunded',
                            'refund_status' => 'completed',
                            'refund_due_amount' => $payment->amount,
                            'refunded_amount' => $payment->amount,
                            'refunded_at' => now(),
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | その他オンライン決済
                    |--------------------------------------------------------------------------
                    |
                    | Stripeでは返金できないため、
                    | 管理者による手動返金待ちにする。
                    |--------------------------------------------------------------------------
                    */ elseif ($payment->payment_method === 'online') {
                        $payment->update([
                            'refund_status' => 'pending',
                            'refund_due_amount' => $payment->amount,
                            'refunded_amount' => null,
                            'refunded_at' => null,
                        ]);
                    }

                    $participant->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'payment_expires_at' => null,
                    ]);

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | 決済待ち
                |--------------------------------------------------------------------------
                */
                if (
                    $participant->status === 'pending_payment'
                    && $payment
                    && $payment->status === 'pending'
                ) {
                    $payment->update([
                        'status' => 'failed',
                        'refund_status' => 'not_required',
                        'refund_due_amount' => 0,
                    ]);
                }

                if ($participant->status !== 'cancelled') {
                    $participant->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'payment_expires_at' => null,
                    ]);
                }
            }
        }
        $event->update($validated);

        return redirect()
            ->route('admin.events.show', $event)
            ->with(
                'success',
                $isBeingCancelled
                ? 'イベントを中止し、支払い済みの参加者へ全額返金しました。'
                : 'イベントを更新しました。'
            );
    }
    public function destroy(Event $event)
    {
        $hasParticipants = $event->participants()->exists();

        if ($event->status !== 'draft' || $hasParticipants) {
            return redirect()
                ->route('admin.events.show', $event)
                ->with(
                    'error',
                    'イベントは、下書き状態かつ参加申込がない場合のみ削除できます。'
                );
        }

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'イベントを削除しました。');
    }
}