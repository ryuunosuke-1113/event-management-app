<?php

namespace App\Http\Controllers;

use App\Models\EventParticipant;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    public function store(Request $request, EventParticipant $eventParticipant)
    {
        if ($eventParticipant->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($eventParticipant->status !== 'pending_payment') {
            return redirect()
                ->route('event-participants.index')
                ->with('error', 'この申し込みは現在決済できません。');
        }

        $eventParticipant->load(['event', 'payment']);

        if (!$eventParticipant->payment) {
            return redirect()
                ->route('event-participants.index')
                ->with('error', '決済情報が見つかりません。');
        }

        $stripe = new StripeClient(
            config('services.stripe.secret')
        );

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',

            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'jpy',

                        'product_data' => [
                            'name' => $eventParticipant->event->title,
                        ],

                        'unit_amount' => $eventParticipant->payment->amount,
                    ],

                    'quantity' => 1,
                ],
            ],
            'expires_at' => now()->addMinutes(30)->timestamp,

            'success_url' => route('checkout.success')
                . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => route('event-participants.index'),

            'metadata' => [
                'event_participant_id' => $eventParticipant->id,
            ],
        ]);

        $eventParticipant->payment->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        return redirect($session->url);
    }

    public function success()
    {
        return view('checkout.success');
    }
}