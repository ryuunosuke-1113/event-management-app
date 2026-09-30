<?php

namespace App\Http\Controllers;

use App\Models\UserModerationAction;
use Illuminate\Http\Request;

class ModerationMessageController extends Controller
{
    public function show(
        Request $request,
        UserModerationAction $action
    ) {
        if ($action->user_id !== $request->user()->id) {
            abort(403);
        }

        if (!$action->is_active) {
            return redirect()
                ->route('moderation.status')
                ->with(
                    'error',
                    'このアカウント制御はすでに終了しています。'
                );
        }

        $action->load([
            'messages.user',
        ]);

        return view(
            'moderation-messages.show',
            compact('action')
        );
    }
    public function store(
        Request $request,
        UserModerationAction $action
    ) {
        if ($action->user_id !== $request->user()->id) {
            abort(403);
        }
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