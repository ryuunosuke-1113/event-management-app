<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = UserReport::with('reportedUser')
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($request) {
                    $query
                        ->where('user_id', '!=', $request->user()->id)
                        ->whereNull('read_at');
                },
            ])
            ->where('reporter_id', $request->user()->id)
            ->latest()
            ->get();

        return view(
            'reports.index',
            compact('reports')
        );
    }
    public function create(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            abort(403);
        }

        return view(
            'reports.create',
            compact('user')
        );
    }

    public function store(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => [
                'required',
                'in:nuisance,inappropriate,impersonation,event_trouble,other',
            ],
            'details' => [
                'required',
                'string',
                'max:3000',
            ],
        ]);

        UserReport::create([
            'reporter_id' => $request->user()->id,
            'reported_user_id' => $user->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'],
            'status' => 'open',
        ]);

        return redirect()
            ->route('profile.show', $user)
            ->with(
                'success',
                '通報を受け付けました。管理者が内容を確認します。'
            );
    }
    public function show(Request $request, UserReport $userReport)
    {
        if ($userReport->reporter_id !== $request->user()->id) {
            abort(403);
        }

        $userReport->messages()
            ->where('user_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $userReport->load([
            'reportedUser',
            'messages.user',
            'messages.attachments',
        ]);
        return view(
            'reports.show',
            compact('userReport')
        );
    }
    public function storeMessage(
        Request $request,
        UserReport $userReport
    ) {
        if ($userReport->reporter_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => [
                'required',
                'string',
                'max:3000',
            ],
            'images' => [
                'nullable',
                'array',
                'max:3',
            ],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $message = $userReport->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        foreach ($request->file('images', []) as $image) {
            $path = $image->store(
                'report-attachments/' . $userReport->id,
                'local'
            );

            $message->attachments()->create([
                'file_path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize(),
            ]);
        }

        return back()->with(
            'success',
            'メッセージを送信しました。'
        );
    }
}