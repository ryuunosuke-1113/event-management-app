<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserModerationAction;
use Illuminate\Http\Request;
use App\Services\AdminNoticeService;
use App\Models\Event;
use App\Services\EventCancellationService;
use Throwable;

class UserModerationController extends Controller
{
    public function __construct(
        private AdminNoticeService $adminNoticeService
    ) {
    }
    public function index(Request $request)
    {
        $actions = UserModerationAction::with([
            'user',
            'messages',
        ])
            ->where('is_active', true)
            ->latest()
            ->get();

        return view(
            'admin.users.moderation.index',
            compact('actions')
        );
    }
    public function warn(Request $request, User $user)
    {
        if ($user->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'comment' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        // 上書き前の制御が存在するか確認
        $currentAction = $user->moderationActions()
            ->where('is_active', true)
            ->latest()
            ->first();

        $this->deactivateCurrentActions(
            $user,
            $request->user()->id
        );

        $newAction = UserModerationAction::create([
            'user_id' => $user->id,
            'admin_id' => $request->user()->id,
            'action_type' => 'warning',
            'comment' => $validated['comment'],
            'is_active' => true,
        ]);
        // 警告ではイベント利用制限なし
        $user->update([
            'account_status' => 'active',
        ]);

        if ($currentAction) {
            $this->adminNoticeService->sendModerationUpdated(
                $user,
                $request->user(),
                $newAction,
                'アカウント制御変更のお知らせ',
                "運営により、現在のアカウント制御が「警告」に変更されました。\n\n"
                . "理由：\n"
                . $validated['comment']
            );
        } else {
            $this->adminNoticeService->sendModerationApplied(
                $user,
                $request->user(),
                $newAction,
                '警告のお知らせ',
                "運営よりアカウントに警告が行われました。\n\n"
                . "理由：\n"
                . $validated['comment']
            );
        }

        return back()->with(
            'success',
            'ユーザーに警告を行いました。'
        );
    }
    public function suspend(
        Request $request,
        User $user,
        EventCancellationService $eventCancellationService
    ) {
        if ($user->is_admin) {
            abort(403);
        }


        $validated = $request->validate([
            'comment' => [
                'required',
                'string',
                'max:2000',
            ],
            'suspension_type' => [
                'required',
                'in:creation_suspended,full_suspended',
            ],
            'cancel_existing_events' => [
                'nullable',
                'boolean',
            ],
        ]);
        $currentAction = $user->moderationActions()
            ->where('is_active', true)
            ->latest()
            ->first();

        $this->deactivateCurrentActions(
            $user,
            $request->user()->id
        );

        $newAction = UserModerationAction::create([
            'user_id' => $user->id,
            'admin_id' => $request->user()->id,
            'action_type' => 'suspension',
            'comment' => $validated['comment'],
            'is_active' => true,
        ]);
        $user->update([
            'account_status' => $validated['suspension_type'],
        ]);

        $restrictionLabel = match ($validated['suspension_type']) {
            'creation_suspended' => 'イベント作成禁止',
            'full_suspended' => 'イベント作成・参加禁止',
        };

        if ($currentAction) {
            $this->adminNoticeService->sendModerationUpdated(
                $user,
                $request->user(),
                $newAction,
                'アカウント制御変更のお知らせ',
                "運営により、アカウント制御が「{$restrictionLabel}」に変更されました。\n\n"
                . "理由：\n"
                . $validated['comment']
            );
        } else {
            $this->adminNoticeService->sendModerationApplied(
                $user,
                $request->user(),
                $newAction,
                'アカウント制限のお知らせ',
                "運営により、アカウントに「{$restrictionLabel}」の制限が適用されました。\n\n"
                . "理由：\n"
                . $validated['comment']
            );
        }
        if ($request->boolean('cancel_existing_events')) {
            $events = Event::query()
                ->where('organizer_id', $user->id)
                ->where('event_date', '>', now())
                ->whereNotIn('status', [
                    'draft',
                    'cancelled',
                    'finished',
                ])
                ->get();

            foreach ($events as $event) {
                try {
                    $eventCancellationService->cancel(
                        $event,
                        $validated['comment']
                    );
                } catch (Throwable $e) {
                    report($e);

                    return back()->with(
                        'error',
                        'イベントの返金または強制中止処理に失敗しました。'
                    );
                }
            }
        }
        return back()->with(
            'success',
            'ユーザーの利用制限を更新しました。'
        );
    }
    public function edit(User $user)
    {
        if ($user->is_admin) {
            abort(403);
        }

        $user->load([
            'moderationActions' => function ($query) {
                $query->latest();
            },
        ]);

        $currentAction = $user->moderationActions()
            ->where('is_active', true)
            ->with([
                'messages.user',
            ])
            ->latest()
            ->first();

        if ($currentAction) {
            $currentAction->messages()
                ->where('user_id', '!=', auth()->id())
                ->whereNull('read_at')
                ->update([
                    'read_at' => now(),
                ]);
        }

        return view(
            'admin.users.moderation.edit',
            compact('user', 'currentAction')
        );
    }
    private function deactivateCurrentActions(
        User $user,
        int $adminId
    ): void {
        $user->moderationActions()
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'ended_at' => now(),
                'ended_by' => $adminId,
            ]);
    }
    public function clearModeration(
        Request $request,
        User $user
    ) {
        if ($user->is_admin) {
            abort(403);
        }

        $this->deactivateCurrentActions(
            $user,
            $request->user()->id
        );

        $user->update([
            'account_status' => 'active',
        ]);

        $this->adminNoticeService->sendModerationLifted(
            $user,
            $request->user(),
            '運営により、現在のアカウント制御が解除されました。'
        );
        return back()->with(
            'success',
            'アカウント制御を解除しました。'
        );
    }
    public function storeMessage(
        Request $request,
        UserModerationAction $action
    ) {
        if (!$action->is_active) {
            return back()->with(
                'error',
                '終了したアカウント制御にはメッセージを送信できません。'
            );
        }

        $validated = $request->validate([
            'body' => [
                'required',
                'string',
                'max:3000',
            ],
        ]);

        $action->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with(
            'success',
            'メッセージを送信しました。'
        );
    }
}