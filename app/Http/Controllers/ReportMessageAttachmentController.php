<?php

namespace App\Http\Controllers;

use App\Models\ReportMessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportMessageAttachmentController extends Controller
{
    public function show(
        Request $request,
        ReportMessageAttachment $attachment
    ) {
        $attachment->load(
            'message.report'
        );

        $report = $attachment->message->report;
        $user = $request->user();

        $canView =
            $user->is_admin
            || $report->reporter_id === $user->id;

        if (!$canView) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->response(
            $attachment->file_path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
            ]
        );
    }
}