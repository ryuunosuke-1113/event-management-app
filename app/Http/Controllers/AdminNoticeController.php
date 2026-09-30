<?php

namespace App\Http\Controllers;

use App\Models\AdminNotice;
use Illuminate\Http\Request;

class AdminNoticeController extends Controller
{
    public function index(Request $request)
    {
        $notices = AdminNotice::with('event')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view(
            'admin-notices.index',
            compact('notices')
        );
    }

    public function show(
        Request $request,
        AdminNotice $adminNotice
    ) {
        if ($adminNotice->user_id !== $request->user()->id) {
            abort(403);
        }

        if (!$adminNotice->read_at) {
            $adminNotice->update([
                'read_at' => now(),
            ]);
        }

        $adminNotice->load([
            'event',
            'moderationAction',
        ]);

        return view(
            'admin-notices.show',
            compact('adminNotice')
        );
    }
}