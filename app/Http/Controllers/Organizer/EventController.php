<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Throwable;
use Illuminate\Support\Facades\Gate;
use App\Services\AdminNoticeService;
use App\Models\User;
use App\Services\EventCancellationService;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $events = Event::with('images')
            ->where('organizer_id', $user->id)
            ->latest()
            ->get();

        $stripeConnectStatus = 'not_connected';

        if ($user->stripe_account_id) {
            try {
                $stripe = new StripeClient(
                    config('services.stripe.secret')
                );

                $account = $stripe->v2->core->accounts->retrieve(
                    $user->stripe_account_id,
                    [
                        'include' => [
                            'configuration.recipient',
                        ],
                    ]
                );

                $transferStatus =
                    $account->configuration?->recipient
                        ?->capabilities?->stripe_balance
                        ?->stripe_transfers?->status;

                if (
                    $account->configuration?->recipient?->applied === true
                    && $transferStatus === 'active'
                ) {
                    $stripeConnectStatus = 'active';
                } else {
                    $stripeConnectStatus = 'incomplete';
                }
            } catch (\Throwable $e) {
                report($e);

                $stripeConnectStatus = 'error';
            }
        }

        return view('organizer.events.index', compact(
            'events',
            'stripeConnectStatus'
        ));
    }
    public function show(Event $event)
    {
        Gate::authorize('update', $event);

        $event->load([
            'images',
            'participants.user.profile',
            'participants.payment',
        ]);
        return view(
            'organizer.events.show',
            compact('event')
        );
    }
    public function create(Request $request)
    {
        $user = $request->user();

        if (
            in_array($user->account_status, [
                'creation_suspended',
                'full_suspended',
            ], true)
        ) {
            return redirect()
                ->route('organizer.events.index')
                ->with(
                    'error',
                    '現在、運営によりイベントの作成が制限されています。'
                );
        }
        return view('organizer.events.create');
    }
    public function store(Request $request)
    {
        $user = $request->user();

        if (
            in_array($user->account_status, [
                'creation_suspended',
                'full_suspended',
            ], true)
        ) {
            return redirect()
                ->route('organizer.events.index')
                ->with(
                    'error',
                    '現在、運営によりイベントの作成が制限されています。'
                );
        }
        $validated = $request->validate(
            [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'event_date' => [
                    'required',
                    'date',
                    'after:now',
                ],
                'place' => ['required', 'string', 'max:255'],
                'capacity' => ['required', 'integer', 'min:1'],
                'price' => ['required', 'integer', 'min:500'],
                'status' => [
                    'required',
                    'in:draft,published,closed,finished,cancelled',
                ],
                'chat_url' => ['nullable', 'url'],
                'images' => ['nullable', 'array'],
                'images.*' => [
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],
            ],
            [
                'event_date.after' =>
                    '開催日時に過去の日時を指定することはできません。',
            ]
        );
        $connectBypass =
            app()->environment('local')
            && config('services.stripe.local_connect_bypass');

        if (
            $validated['status'] === 'published'
            && !$connectBypass
            && !$this->hasActiveStripeConnect($request->user())
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'イベントを公開するには、Stripe連携を完了してください。'
                );
        }
        $validated['organizer_id'] = $request->user()->id;

        $event = Event::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store(
                    'event-images',
                    'public'
                );

                $event->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        $conversation = $event->conversations()->firstOrCreate([
            'type' => 'event',
        ]);

        $conversation->members()->firstOrCreate([
            'user_id' => $event->organizer_id,
        ]);

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'イベントを作成しました。');
    }
    public function edit(Event $event)
    {
        Gate::authorize('update', $event);

        return view('organizer.events.edit', compact('event'));
    }
    public function update(
        Request $request,
        Event $event,
        EventCancellationService $eventCancellationService
    ) {
        Gate::authorize('update', $event);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'place' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:500'],
            'status' => ['required', 'in:draft,published,closed,finished,cancelled'],
            'chat_url' => ['nullable', 'url'],
            'images' => ['nullable', 'array'],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);
        $newEventDate = Carbon::parse($validated['event_date']);

        $eventDateChanged = !$newEventDate->equalTo(
            $event->event_date
        );

        if (
            $eventDateChanged
            && $newEventDate->lte(now())
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'event_date' => '開催日時を過去の日時に変更することはできません。',
                ]);
        }
        $connectBypass =
            app()->environment('local')
            && config('services.stripe.local_connect_bypass');

        if (
            $validated['status'] === 'published'
            && !$connectBypass
            && !$this->hasActiveStripeConnect($request->user())
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'イベントを公開するには、Stripe連携を完了してください。'
                );
        }

        // 一度中止したイベントを再公開しない
        if (
            $event->status === 'cancelled'
            && $validated['status'] !== 'cancelled'
        ) {
            return back()
                ->withInput()
                ->with('error', '中止済みのイベントを再公開することはできません。');
        }

        $isBeingCancelled =
            $event->status !== 'cancelled'
            && $validated['status'] === 'cancelled';


        if ($isBeingCancelled) {
            try {
                $eventCancellationService->cancel(
                    $event,
                    '主催者都合によるイベント中止'
                );
            } catch (Throwable $e) {
                report($e);

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        '返金またはイベント中止処理に失敗しました。イベントは中止されていません。'
                    );
            }
        }
        if (!$isBeingCancelled) {
            $event->update($validated);
        }
        if ($request->hasFile('images')) {
            $nextSortOrder = ($event->images()->max('sort_order') ?? -1) + 1;

            foreach ($request->file('images') as $index => $image) {
                $path = $image->store(
                    'event-images',
                    'public'
                );

                $event->images()->create([
                    'image_path' => $path,
                    'sort_order' => $nextSortOrder + $index,
                ]);
            }
        }

        return redirect()
            ->route('organizer.events.index')
            ->with(
                'success',
                $isBeingCancelled
                ? 'イベントを中止し、支払い済みの参加者へ全額返金しました。'
                : 'イベントを更新しました。'
            );
    }
    public function destroyImage(Event $event, EventImage $eventImage)
    {
        Gate::authorize('update', $event);

        if ($eventImage->event_id !== $event->id) {
            abort(404);
        }

        Storage::disk('public')->delete($eventImage->image_path);

        $eventImage->delete();

        return back()
            ->with('success', 'イベント画像を削除しました。');
    }

    public function makePrimaryImage(Event $event, EventImage $eventImage)
    {
        Gate::authorize('update', $event);

        if ($eventImage->event_id !== $event->id) {
            abort(404);
        }

        $event->images()->increment('sort_order');

        $eventImage->update([
            'sort_order' => 0,
        ]);

        return back()
            ->with('success', 'この画像を1枚目に設定しました。');
    }
    public function destroy(Event $event)
    {
        Gate::authorize('update', $event);

        $hasParticipants = $event->participants()->exists();

        if ($event->status !== 'draft' || $hasParticipants) {
            return redirect()
                ->route('organizer.events.show', $event)
                ->with(
                    'error',
                    'イベントは、下書き状態かつ参加申込がない場合のみ削除できます。'
                );
        }

        $event->delete();

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'イベントを削除しました。');
    }
    public function archive(Event $event)
    {
        Gate::authorize('update', $event);

        if (!in_array($event->status, ['finished', 'cancelled'], true)) {
            return back()->with(
                'error',
                '開催終了または中止したイベントのみアーカイブできます。'
            );
        }

        $event->update([
            'archived_at' => now(),
        ]);

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'イベントをアーカイブしました。');
    }

    public function restoreArchive(Event $event)
    {
        Gate::authorize('update', $event);

        $event->update([
            'archived_at' => null,
        ]);

        return redirect()
            ->route('organizer.events.archived')
            ->with('success', 'イベントのアーカイブを解除しました。');
    }
    public function archived(Request $request)
    {
        $events = Event::with('images')
            ->where('organizer_id', $request->user()->id)
            ->whereNotNull('archived_at')
            ->orderByDesc('event_date')
            ->get();

        return view('organizer.events.archived', compact('events'));
    }
    private function hasActiveStripeConnect($user): bool
    {
        if (!$user->stripe_account_id) {
            return false;
        }

        try {
            $stripe = new StripeClient(
                config('services.stripe.secret')
            );

            $account = $stripe->v2->core->accounts->retrieve(
                $user->stripe_account_id,
                [
                    'include' => [
                        'configuration.recipient',
                    ],
                ]
            );

            $transferStatus =
                $account->configuration?->recipient
                    ?->capabilities?->stripe_balance
                    ?->stripe_transfers?->status;

            return
                $account->configuration?->recipient?->applied === true
                && $transferStatus === 'active';

        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}