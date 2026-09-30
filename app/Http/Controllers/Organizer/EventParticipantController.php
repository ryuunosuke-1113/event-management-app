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
}