<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSetting;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function edit(Request $request)
    {
        $setting = MaintenanceSetting::firstOrCreate(
            ['id' => 1],
            [
                'is_active' => false,
                'message' => 'サービス改善のため、ただいまメンテナンス中です。',
            ]
        );

        if ($request->filled('from')) {
            session([
                'maintenance_return_url' => $request->query('from'),
            ]);
        }

        return view('admin.maintenance.edit', compact('setting'));
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
            'ends_at' => [
                'nullable',
                'date',
                'required_if:is_active,1',
            ],
            'message' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $setting = MaintenanceSetting::firstOrCreate(
            ['id' => 1]
        );

        $setting->update([
            'is_active' => $validated['is_active'],
            'ends_at' => $validated['is_active']
                ? $validated['ends_at']
                : null,
            'message' => $validated['message']
                ?? 'サービス改善のため、ただいまメンテナンス中です。',
            'starts_at' => $validated['is_active']
                ? now()
                : null,
        ]);

        $returnUrl = session()->pull(
            'maintenance_return_url',
            route('admin.maintenance.edit')
        );

        return redirect($returnUrl)
            ->with(
                'success',
                $validated['is_active']
                ? 'メンテナンスを開始しました。'
                : 'メンテナンスを終了しました。'
            );
    }
}