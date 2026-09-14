<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\DirectChatRelation;
use Illuminate\Http\RedirectResponse;

class EventParticipantController extends Controller
{
    public function updateAttendance(
        Request $request,
        EventParticipant $eventParticipant
    ) {
        $event = Event::findOrFail($eventParticipant->event_id);

        Gate::authorize('update', $event);

        $validated = $request->validate([
            'attended' => ['required', 'boolean'],
        ]);

        $eventParticipant->attended_at =
            $validated['attended'] ? now() : null;

        $eventParticipant->save();

        return redirect()
            ->route('organizer.events.show', $event)
            ->with(
                'success',
                $validated['attended']
                ? '参加確認を記録しました。'
                : '参加確認を解除しました。'
            );
    }
    public function confirmOnlinePayment(
        EventParticipant $eventParticipant
    ): RedirectResponse {
        $event = Event::findOrFail($eventParticipant->event_id);

        Gate::authorize('update', $event);

        return DB::transaction(function () use ($eventParticipant) {
            $eventParticipant = EventParticipant::with('payment')
                ->whereKey($eventParticipant->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($eventParticipant->status === 'cancelled') {
                return back()
                    ->with('error', 'キャンセル済みの参加申し込みは確定できません。');
            }

            if ($eventParticipant->status === 'confirmed') {
                return back()
                    ->with('error', 'この参加者はすでに参加確定しています。');
            }

            $payment = $eventParticipant->payment;

            if (!$payment) {
                return back()
                    ->with('error', '決済情報が見つかりません。');
            }

            $event = Event::whereKey($eventParticipant->event_id)
                ->lockForUpdate()
                ->firstOrFail();

            $confirmedCount = $event->participants()
                ->where('status', 'confirmed')
                ->count();

            if ($confirmedCount >= $event->capacity) {
                return back()
                    ->with(
                        'error',
                        'すでに定員に達しているため、参加確定できません。必要に応じてオンライン決済の返金を行ってください。'
                    );
            }

            $payment->update([
                'payment_method' => 'online',
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

            return back()
                ->with('success', 'オンライン決済を確認し、参加確定しました。');
        });
    }
    public function completeRefund(
        EventParticipant $eventParticipant
    ): RedirectResponse {
        $event = Event::findOrFail($eventParticipant->event_id);

        Gate::authorize('update', $event);

        $eventParticipant->load('payment');

        $payment = $eventParticipant->payment;

        if (!$payment) {
            return back()
                ->with('error', '決済情報が見つかりません。');
        }

        if ($payment->payment_method !== 'online') {
            return back()
                ->with('error', 'この操作はその他オンライン決済にのみ使用できます。');
        }

        if ($payment->refund_status !== 'pending') {
            return back()
                ->with('error', 'この決済は返金待ち状態ではありません。');
        }

        if (
            !$payment->refund_due_amount
            || $payment->refund_due_amount <= 0
        ) {
            return back()
                ->with('error', '返金額が設定されていません。');
        }

        $payment->update([
            'status' => 'refunded',
            'refund_status' => 'completed',
            'refunded_amount' => $payment->refund_due_amount,
            'refunded_at' => now(),
        ]);

        return back()
            ->with('success', '返金対応を完了として記録しました。');
    }
}