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
            ->latest()
            ->get();

        return view('event-participants.index', compact('participants'));
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
            | Stripe決済
            |--------------------------------------------------------------------------
            */
            if (
                $payment->payment_method === 'stripe'
                && $refundRate > 0
            ) {
                $stripe = new StripeClient(
                    config('services.stripe.secret')
                );

                $stripe->refunds->create([
                    'payment_intent' => $payment->stripe_payment_intent_id,
                    'amount' => $refundAmount,
                ]);

                $payment->update([
                    'status' => 'refunded',
                    'refunded_at' => $cancelledAt,
                    'refunded_amount' => $refundAmount,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | その他オンライン決済
            |--------------------------------------------------------------------------
            |
            | ここでは自動返金しない。
            | 管理者画面で必要返金額を表示し、
            | 実際に返金したあと「返金対応完了」にする。
            |
            */
            if ($payment->payment_method === 'online') {
                // この時点ではPaymentを変更しない
            }
        }

        $eventParticipant->update([
            'status' => 'cancelled',
            'cancelled_at' => $cancelledAt,
        ]);

        return redirect()
            ->route('event-participants.index')
            ->with('success', '参加申し込みをキャンセルしました。');
    }
}