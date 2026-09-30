<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModerationStatusController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $actions = $user->moderationActions()
            ->with(['admin', 'endedBy'])
            ->latest()
            ->get();

        return view(
            'moderation.status',
            compact('user', 'actions')
        );
    }
}