<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with('user')
            ->latest()
            ->get();

        return view(
            'admin.feedbacks.index',
            compact('feedbacks')
        );
    }

    public function show(Feedback $feedback)
    {
        $feedback->load('user');

        if (is_null($feedback->read_at)) {
            $feedback->update([
                'read_at' => now(),
            ]);
        }

        return view(
            'admin.feedbacks.show',
            compact('feedback')
        );
    }
}