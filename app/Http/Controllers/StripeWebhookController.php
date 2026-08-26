<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;
use App\Models\Event;
use Stripe\StripeClient;
use App\Models\DirectChatRelation;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $secret
            );
        } catch (UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 決済成功
        |--------------------------------------------------------------------------
        */

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            DB::transaction(function () use ($session) {
                $payment = Payment::where(
                    'stripe_checkout_session_id',
                    $session->id
                )
                    ->lockForUpdate()
                    ->first();

                if (!$payment) {
                    return;
                }

                if (in_array($payment->status, ['paid', 'refunded'], true)) {
                    return;
                }

                $eventParticipant = $payment->eventParticipant;

                $event = Event::whereKey($eventParticipant->event_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $confirmedCount = $event->participants()
                    ->where('status', 'confirmed')
                    ->count();

                if ($confirmedCount < $event->capacity) {
                    $payment->update([
                        'stripe_payment_intent_id' => $session->payment_intent,
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    $eventParticipant->update([
                        'status' => 'confirmed',
                        'payment_expires_at' => null,
                    ]);

                    $conversation = $event->conversations()
                        ->where('type', 'event')
                        ->first();

                    if ($conversation) {
                        $conversation->members()->firstOrCreate([
                            'user_id' => $eventParticipant->user_id,
                        ]);
                    }

                    DirectChatRelation::createForConfirmedParticipant(
                        $eventParticipant
                    );

                    return;
                }
                // 満員だった場合は全額返金
                $stripe = new StripeClient(
                    config('services.stripe.secret')
                );

                $stripe->refunds->create(
                    [
                        'payment_intent' => $session->payment_intent,
                    ],
                    [
                        'idempotency_key' => 'capacity-refund-' . $payment->id,
                    ]
                );

                $payment->update([
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'status' => 'refunded',
                    'paid_at' => now(),
                    'refunded_at' => now(),
                ]);

                $eventParticipant->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'capacity_reached_after_payment',
                    'payment_expires_at' => null,
                ]);
            });
        }        /*
         |--------------------------------------------------------------------------
         | Checkout期限切れ
         |--------------------------------------------------------------------------
         */

        if ($event->type === 'checkout.session.expired') {
            $session = $event->data->object;

            DB::transaction(function () use ($session) {
                $payment = Payment::where(
                    'stripe_checkout_session_id',
                    $session->id
                )->first();

                if (!$payment) {
                    return;
                }

                if ($payment->status !== 'pending') {
                    return;
                }

                $payment->update([
                    'status' => 'failed',
                ]);

                $payment->eventParticipant->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'payment_expires_at' => null,
                ]);
            });
        }

        return response()->json([
            'received' => true,
        ]);
    }
}