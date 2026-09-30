<?php

namespace App\Http\Controllers;

use App\Models\EventParticipant;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    public function store(
        Request $request,
        EventParticipant $eventParticipant
    ) {
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

        $organizer = $eventParticipant->event->organizer;

        $connectBypass =
            app()->environment('local')
            && config('services.stripe.local_connect_bypass');

        /*
         * 通常時は主催者のStripe Connectが必須。
         * ローカルのバイパス時だけ、このチェックを省略する。
         */
        if (
            !$connectBypass
            && (!$organizer || !$organizer->stripe_account_id)
        ) {
            return redirect()
                ->route('event-participants.index')
                ->with('error', '主催者のStripe連携が完了していません。');
        }

        $amount = $eventParticipant->payment->amount;

        $stripe = new StripeClient(
            config('services.stripe.secret')
        );

        $sessionData = [
            'mode' => 'payment',

            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'jpy',

                        'product_data' => [
                            'name' => $eventParticipant->event->title,
                        ],

                        'unit_amount' => $amount,
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
        ];

        /*
         * 本来のConnect決済の場合だけ、
         * プラットフォーム手数料と主催者への送金を設定する。
         */
        if (!$connectBypass) {
            $platformFeePercent = (float) config(
                'services.stripe.platform_fee_percent',
                3
            );

            $applicationFee = (int) round(
                $amount * ($platformFeePercent / 100)
            );

            $sessionData['payment_intent_data'] = [
                'application_fee_amount' => $applicationFee,

                'transfer_data' => [
                    'destination' => $organizer->stripe_account_id,
                ],
            ];
        }


        $session = $stripe->checkout->sessions->create(
            $sessionData
        );

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