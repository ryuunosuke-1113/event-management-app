<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;

class StripeConnectController extends Controller
{
    public function connect(Request $request)
    {
        $user = $request->user();

        $stripe = new StripeClient(
            config('services.stripe.secret')
        );
        $profile = [
            'product_description' => 'イベント参加費の受け取り',
        ];

        if (app()->environment('production')) {
            $profile['business_url'] = route('profile.show', $user);
        }

        // Connected Accountをまだ持っていなければ作成
        if (!$user->stripe_account_id) {

            $account = $stripe->v2->core->accounts->create([
                'contact_email' => $user->email,
                'display_name' => $user->name,

                'dashboard' => 'express',

                'identity' => [
                    'country' => 'jp',
                ],

                'defaults' => [
                    'profile' => $profile,

                    'responsibilities' => [
                        'fees_collector' => 'application',
                        'losses_collector' => 'application',
                    ],
                ],
                'configuration' => [
                    'recipient' => [
                        'capabilities' => [
                            'stripe_balance' => [
                                'stripe_transfers' => [
                                    'requested' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            $user->update([
                'stripe_account_id' => $account->id,
            ]);
        }

        $accountLink = $stripe->v2->core->accountLinks->create([
            'account' => $user->stripe_account_id,

            'use_case' => [
                'type' => 'account_onboarding',

                'account_onboarding' => [
                    'configurations' => [
                        'recipient',
                    ],

                    // 今必要な情報だけ収集
                    'collection_options' => [
                        'fields' => 'currently_due',
                        'future_requirements' => 'omit',
                    ],

                    'refresh_url'
                    => route('stripe.connect.refresh'),

                    'return_url'
                    => route('stripe.connect.return'),
                ],
            ],
        ]);

        return redirect($accountLink->url);
    }

    public function refresh(Request $request)
    {
        return redirect()
            ->route('stripe.connect.start');
    }

    public function return(Request $request)
    {
        return redirect()
            ->route('organizer.events.index')
            ->with(
                'success',
                'Stripe連携画面から戻りました。'
            );
    }
    public function guide(Request $request)
    {
        $user = $request->user();

        if ($user->stripe_account_id) {
            return redirect()
                ->route('organizer.events.index')
                ->with(
                    'success',
                    'Stripe連携はすでに設定されています。'
                );
        }

        return view('stripe.connect-guide');
    }
}