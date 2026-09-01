<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class EventParticipantController extends Controller
{
    public function index(Request $request)
    {
        $participants = EventParticipant::with(['event', 'payment'])
            ->where('user_id', $request->user()->id)
            ->get();

        /*
         * これから参加するイベント
         *
         * 参加者自身のキャンセル・期限切れ等で
         * participant.status = cancelled のものは表示しない。
         */
        $upcomingParticipants = $participants
            ->filter(function ($participant) {
                return $participant->status !== 'cancelled'
                    && !in_array(
                        $participant->event->status,
                        ['finished', 'cancelled'],
                        true
                    );
            })
            ->sortBy(function ($participant) {
                return $participant->event->event_date;
            })
            ->values();

        /*
         * 開催終了・主催者都合で中止になったイベント
         *
         * event.status が cancelled なら、
         * participant.status が cancelled でも表示する。
         */
        $pastParticipants = $participants
            ->filter(function ($participant) {
                return in_array(
                    $participant->event->status,
                    ['finished', 'cancelled'],
                    true
                );
            })
            ->sortByDesc(function ($participant) {
                return $participant->event->event_date;
            })
            ->values();

        return view('event-participants.index', compact(
            'upcomingParticipants',
            'pastParticipants'
        ));
    }
    public function cancelled(Request $request)
    {
        $participants = EventParticipant::with(['event', 'payment'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'cancelled')
            ->where('cancellation_reason', 'user_cancelled')
            ->orderByDesc('cancelled_at')
            ->get();

        return view(
            'event-participants.cancelled',
            compact('participants')
        );
    }
    public function store(Request $request, Event $event)
    {
        if ($event->status !== 'published') {
            abort(404);
        }
        $confirmedCount = EventParticipant::where('event_id', $event->id)
            ->where('status', 'confirmed')
            ->count();

        if ($confirmedCount >= $event->capacity) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', 'このイベントは定員に達しています。');
        }

        $participant = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($participant) {

            if ($participant->status === 'cancelled') {
                $participant->update([
                    'status' => 'pending_payment',
                    'cancelled_at' => null,
                    'payment_expires_at' => now()->addMinutes(30),
                ]);
                if ($participant->payment) {
                    $participant->payment->update([
                        'amount' => $event->price,
                        'status' => 'pending',
                        'stripe_checkout_session_id' => null,
                        'stripe_payment_intent_id' => null,
                        'paid_at' => null,
                        'refunded_at' => null,
                    ]);
                } else {
                    $participant->payment()->create([
                        'amount' => $event->price,
                        'status' => 'pending',
                    ]);
                }

                return redirect()
                    ->route('events.show', $event)
                    ->with('success', 'イベントに再申し込みしました。');
            }
            return redirect()
                ->route('events.show', $event)
                ->with('error', 'すでにこのイベントに申し込んでいます。');
        }

        $participant = EventParticipant::create([
            'event_id' => $event->id,
            'user_id' => $request->user()->id,
            'status' => 'pending_payment',
            'payment_expires_at' => now()->addMinutes(30),
        ]);
        $participant->payment()->create([
            'amount' => $event->price,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('success', '参加申し込みを受け付けました。');
    }
    public function confirmCancel(Request $request, EventParticipant $eventParticipant)
    {
        if ($eventParticipant->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($eventParticipant->status === 'cancelled') {
            return redirect()
                ->route('event-participants.index')
                ->with('error', 'この申し込みはすでにキャンセルされています。');
        }

        $eventParticipant->load(['event', 'payment']);

        $refundRate = 0;
        $refundAmount = 0;

        if (
            $eventParticipant->status === 'confirmed'
            && $eventParticipant->payment
            && $eventParticipant->payment->status === 'paid'
        ) {
            $refundRate = $eventParticipant->event->refundRateAt(now());

            $refundAmount = $eventParticipant->event->refundAmountAt(
                now(),
                $eventParticipant->payment->amount
            );
        }

        return view('event-participants.cancel-confirm', [
            'eventParticipant' => $eventParticipant,
            'refundRate' => $refundRate,
            'refundAmount' => $refundAmount,
        ]);
    }
    public function destroy(Request $request, EventParticipant $eventParticipant)
    {
        if ($eventParticipant->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($eventParticipant->status === 'cancelled') {
            return redirect()
                ->route('event-participants.index')
                ->with('error', 'この申し込みはすでにキャンセルされています。');
        }

        $eventParticipant->load(['event', 'payment']);

        $payment = $eventParticipant->payment;
        $cancelledAt = now();

        if (
            $eventParticipant->status === 'confirmed'
            && $payment
            && $payment->status === 'paid'
        ) {
            $refundRate = $eventParticipant->event->refundRateAt($cancelledAt);

            $refundAmount = $eventParticipant->event->refundAmountAt(
                $cancelledAt,
                $payment->amount
            );

            /*
            |--------------------------------------------------------------------------
            | 返金なし
            |--------------------------------------------------------------------------
            */
            if ($refundAmount <= 0) {
                $payment->update([
                    'refund_status' => 'not_required',
                    'refund_due_amount' => 0,
                    'refunded_amount' => null,
                    'refunded_at' => null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Stripe決済
            |--------------------------------------------------------------------------
            */ elseif ($payment->payment_method === 'stripe') {
                if (!$payment->stripe_payment_intent_id) {
                    return back()->with(
                        'error',
                        'Stripeの決済情報が見つからないため、キャンセル処理を完了できませんでした。'
                    );
                }

                $stripe = new StripeClient(
                    config('services.stripe.secret')
                );

                $stripe->refunds->create([
                    'payment_intent' => $payment->stripe_payment_intent_id,
                    'amount' => $refundAmount,
                ]);

                $payment->update([
                    'status' => 'refunded',
                    'refund_status' => 'completed',
                    'refund_due_amount' => $refundAmount,
                    'refunded_amount' => $refundAmount,
                    'refunded_at' => $cancelledAt,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | その他オンライン決済
            |--------------------------------------------------------------------------
            |
            | 自動返金はできないため、
            | 管理者が返金するまで「返金待ち」とする。
            |--------------------------------------------------------------------------
            */ elseif ($payment->payment_method === 'online') {
                $payment->update([
                    'refund_status' => 'pending',
                    'refund_due_amount' => $refundAmount,
                    'refunded_amount' => null,
                    'refunded_at' => null,
                ]);
            }
        }
        $eventParticipant->update([
            'status' => 'cancelled',
            'cancelled_at' => $cancelledAt,
            'cancellation_reason' => 'user_cancelled',
        ]);
        return redirect()
            ->route('event-participants.index')
            ->with('success', '参加申し込みをキャンセルしました。');
    }
}