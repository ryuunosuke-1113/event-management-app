<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function edit(Request $request)
    {
        return view('account.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'photo' => [
                'nullable',
                'image',
                'max:5120',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'current_password' => [
                'nullable',
                'required_with:password',
                'current_password',
            ],

            'password' => [
                'nullable',
                'confirmed',
                'min:8',
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $profile = $user->profile()->firstOrCreate();

        if ($request->hasFile('photo')) {
            $profile->photo_path = $request->file('photo')
                ->store('profiles', 'public');
        }

        $profile->bio = $validated['bio'] ?? null;
        $profile->save();

        return redirect()
            ->route('account.edit')
            ->with('success', 'アカウント情報を更新しました。');
    }
}