<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\DirectChatRelation;

class ProfileController extends Controller
{
    public function show(\App\Models\User $user)
    {
        $user->load('profile');

        $canDirectChat = false;

        if (auth()->check() && auth()->id() !== $user->id) {
            $currentUserId = auth()->id();

            $firstUserId = min($currentUserId, $user->id);
            $secondUserId = max($currentUserId, $user->id);

            $canDirectChat = DirectChatRelation::where(
                'user_id',
                $firstUserId
            )
                ->where(
                    'related_user_id',
                    $secondUserId
                )
                ->exists();
        }

        return view(
            'profile.show',
            compact(
                'user',
                'canDirectChat'
            )
        );
    }
    public function hostedEvents(\App\Models\User $user)
    {
        $events = \App\Models\Event::where(
            'organizer_id',
            $user->id
        )
            ->whereNotIn('status', [
                'draft',
                'cancelled',
            ])
            ->orderByDesc('event_date')
            ->get();

        return view(
            'profile.hosted-events',
            compact('user', 'events')
        );
    }

    public function participatedEvents(\App\Models\User $user)
    {
        $user->load('profile');

        $canShowParticipationHistory =
            auth()->id() === $user->id
            || ($user->profile?->show_participation_history ?? true);

        if (!$canShowParticipationHistory) {
            abort(403);
        }

        $events = \App\Models\Event::whereHas(
            'participants',
            function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'confirmed');
            }
        )
            ->where('event_date', '<', now())
            ->orderByDesc('event_date')
            ->get();

        return view(
            'profile.participated-events',
            compact('user', 'events')
        );
    }
    public function edit(Request $request)
    {
        $user = $request->user();

        $profile = $user->profile()->firstOrCreate([]);

        return view('profile.edit', compact('user', 'profile'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $profile = $user->profile()->firstOrCreate([]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'show_participation_history' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
        ]);

        $profileData = [
            'bio' => $validated['bio'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }

            $profileData['photo_path'] = $request->file('photo')
                ->store('profiles', 'public');
        }
        $validated['show_participation_history'] =
            $request->boolean('show_participation_history');

        $profile->update($profileData);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'プロフィールを更新しました。');
    }
}