<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    public function index(Request $request)
    {
        $query = UserReport::with([
            'reporter',
            'reportedUser',
            'handler',
        ])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($request) {
                    $query
                        ->where('user_id', '!=', $request->user()->id)
                        ->whereNull('read_at');
                },
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query
            ->latest()
            ->get();

        return view(
            'admin.reports.index',
            compact('reports')
        );
    }
    public function show(Request $request, UserReport $userReport)
    {
        $userReport->messages()
            ->where('user_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $userReport->load([
            'reporter',
            'reportedUser',
            'handler',
            'messages.user',
            'messages.attachments',
        ]);
        return view(
            'admin.reports.show',
            compact('userReport')
        );
    }
    public function updateStatus(Request $request, UserReport $userReport)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:open,in_progress,resolved',
            ],
        ]);

        $userReport->update([
            'status' => $validated['status'],
            'handled_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            '通報の対応状況を更新しました。'
        );
    }
    public function storeMessage(
        Request $request,
        UserReport $userReport
    ) {
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