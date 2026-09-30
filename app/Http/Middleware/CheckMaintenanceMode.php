<?php

namespace App\Http\Middleware;

use App\Models\MaintenanceSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $setting = MaintenanceSetting::first();

        if (!$setting || !$setting->is_active) {
            return $next($request);
        }

        // Stripe Webhookは止めない
        if ($request->is('stripe/webhook')) {
            return $next($request);
        }

        // ログイン関連はメンテナンス中でも利用可能
        if (
            $request->is('login')
            || $request->is('register')
            || $request->is('forgot-password')
            || $request->is('reset-password/*')
        ) {
            return $next($request);
        }

        // 管理者は通常どおり利用可能
        if ($request->user()?->is_admin) {
            return $next($request);
        }

        return response()
            ->view('maintenance', [
                'setting' => $setting,
            ], 503);
    }
}