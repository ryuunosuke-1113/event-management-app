<?php

namespace App\Http\Controllers\Admin;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Models\EventParticipant;
use Illuminate\Http\RedirectResponse;
use App\Models\DirectChatRelation;
use Illuminate\Http\Request;

class EventParticipantController extends Controller
{
    public function confirm(EventParticipant $eventParticipant): RedirectResponse
    {
        $eventParticipant->load('payment');

        if ($eventParticipant->status === 'cancelled') {
            return back()
                ->with('error', 'キャンセル済みの参加申し込みは参加確定にできません。');
        }

        if (
            !$eventParticipant->payment
            || $eventParticipant->payment->status !== 'paid'
        ) {
            return back()
                ->with('error', '支払い済みではないため、参加確定にできません。');
        }

        $eventParticipant->update([
            'status' => 'confirmed',
            'payment_expires_at' => null,
        ]);

        return back()
            ->with('success', '参加者を参加確定にしました。');
    }

    public function cancelPending(EventParticipant $eventParticipant): RedirectResponse
    {
        $eventParticipant->load('payment');

        if ($eventParticipant->status !== 'pending_payment') {
            return back()
                ->with('error', '決済待ちの参加申し込みだけキャンセルできます。');
        }

        if (
            $eventParticipant->payment
            && $eventParticipant->payment->status === 'paid'
        ) {
            return back()
                ->with('error', '支払い済みの参加者はこの操作ではキャンセルできません。');
        }

        $eventParticipant->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'payment_expires_at' => null,
        ]);

        if (
            $eventParticipant->payment
            && $eventParticipant->payment->status === 'pending'
        ) {
            $eventParticipant->payment->update([
                'status' => 'failed',
            ]);
        }

        return back()
            ->with('success', '決済待ちの参加申し込みをキャンセルしました。');
    }
    public function confirmOnlinePayment(EventParticipant $eventParticipant): RedirectResponse
    {
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
    public function updateAttendance(
        Request $request,
        EventParticipant $eventParticipant
    ) {
        $validated = $request->validate([
            'attended' => ['required', 'boolean'],
        ]);

        $eventParticipant->attended_at =
            $validated['attended'] ? now() : null;

        $eventParticipant->save();

        return redirect()
            ->route('admin.events.show', $eventParticipant->event_id)
            ->with(
                'success',
                $validated['attended']
                ? '参加確認を記録しました。'
                : '参加確認を解除しました。'
            );
    }
}