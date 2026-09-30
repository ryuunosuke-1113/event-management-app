<?php

namespace App\Services;

use App\Models\Event;
use Stripe\StripeClient;
use Throwable;

class EventCancellationService
{
    public function __construct(
        private AdminNoticeService $adminNoticeService
    ) {
    }

    public function cancel(
        Event $event,
        string $reason
    ): void {
        $event->load([
            'participants.payment',
            'participants.user',
        ]);
        $noticeRecipients = $event->participants
            ->where('status', 'confirmed')
            ->pluck('user_id')
            ->unique()
            ->values();

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
                if ($payment->payment_method === 'stripe') {
                    if (!$payment->stripe_payment_intent_id) {
                        throw new \RuntimeException(
                            'Stripeの決済情報が見つからない参加者がいます。'
                        );
                    }

                    $connectBypass =
                        app()->environment('local')
                        && config('services.stripe.local_connect_bypass');

                    $refundData = [
                        'payment_intent' => $payment->stripe_payment_intent_id,
                    ];

                    if (!$connectBypass) {
                        $refundData['reverse_transfer'] = true;
                        $refundData['refund_application_fee'] = true;
                    }

                    try {
                        $stripe->refunds->create($refundData);
                    } catch (Throwable $e) {
                        report($e);

                        throw new \RuntimeException(
                            'Stripeの返金処理に失敗しました。',
                            previous: $e
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

        // イベント本体を中止
        $event->update([
            'status' => 'cancelled',
        ]);

        // イベントチャットを閉鎖
        $event->conversations()
            ->where('type', 'event')
            ->update([
                'is_closed' => true,
                'closed_at' => now(),
                'closed_reason' => $reason,
            ]);

        $users = \App\Models\User::whereIn(
            'id',
            $noticeRecipients
        )->get();

        foreach ($users as $recipient) {
            $this->adminNoticeService->sendEventCancelled(
                $recipient,
                null,
                $event,
                "「{$event->title}」は中止となりました。\n\n"
                . "理由：\n"
                . $reason
            );
        }
    }
}