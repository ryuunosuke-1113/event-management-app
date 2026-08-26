<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Password;

use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors([
                'email' => __($status),
            ]);
    }
}