<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $feedbacks = Feedback::where(
            'user_id',
            $request->user()->id
        )
            ->latest()
            ->get();

        return view(
            'feedbacks.index',
            compact('feedbacks')
        );
    }
    public function create()
    {
        return view('feedbacks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'body' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        Feedback::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        return redirect()
            ->route('feedback.create')
            ->with(
                'success',
                'フィードバックを送信しました。ありがとうございます。'
            );
    }
}