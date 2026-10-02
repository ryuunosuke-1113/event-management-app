<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\EventParticipantController;
use App\Http\Controllers\Admin\EventParticipantController as AdminEventParticipantController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventChatController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DirectChatController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\Organizer\EventController as OrganizerEventController;
use App\Http\Controllers\Organizer\EventParticipantController as OrganizerEventParticipantController;
use App\Http\Controllers\StripeConnectController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\Admin\UserReportController as AdminUserReportController;
use App\Http\Controllers\ReportMessageAttachmentController;
use App\Http\Controllers\Admin\UserModerationController;
use App\Http\Controllers\ModerationStatusController;
use App\Http\Controllers\AdminNoticeController;
use App\Http\Controllers\ModerationMessageController;
use App\Http\Controllers\FeedbackController;



/*
|--------------------------------------------------------------------------
| 公開ルート
|--------------------------------------------------------------------------
|
| ログインしていなくてもアクセスできます。
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/events', [PublicEventController::class, 'index'])
    ->name('events.index');

Route::get('/events/{event}', [PublicEventController::class, 'show'])
    ->name('events.show');
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');
Route::get('/events/{event}/login', function (\App\Models\Event $event) {
    session()->put('url.intended', route('events.show', $event));

    return redirect()->route('login');
})->name('events.login');
Route::get(
    '/profiles/{user}',
    [ProfileController::class, 'show']
)->name('profile.show');
Route::get(
    '/profiles/{user}/hosted-events',
    [ProfileController::class, 'hostedEvents']
)->name('profile.hosted-events');

Route::get(
    '/profiles/{user}/participated-events',
    [ProfileController::class, 'participatedEvents']
)->name('profile.participated-events');
Route::view(
    '/terms',
    'legal.terms'
)->name('terms');

Route::view(
    '/privacy',
    'legal.privacy'
)->name('privacy');

Route::view(
    '/commercial-transactions',
    'legal.commercial-transactions'
)->name('commercial-transactions');

Route::view(
    '/contact',
    'legal.contact'
)->name('contact');



/*
|--------------------------------------------------------------------------
| 未ログインユーザー専用
|--------------------------------------------------------------------------
|
| ログイン済みユーザーは基本的にアクセスしません。
|
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'store'])
        ->name('register.store');
    Route::get(
        '/forgot-password',
        [ForgotPasswordController::class, 'create']
    )->name('password.request');
    Route::post(
        '/forgot-password',
        [ForgotPasswordController::class, 'store']
    )->name('password.email');
    Route::get(
        '/reset-password/{token}',
        function (string $token) {
            return view('auth.reset-password', [
                'token' => $token,
                'email' => request('email'),
            ]);
        }
    )->name('password.reset');
    Route::post(
        '/reset-password',
        [ResetPasswordController::class, 'store']
    )->name('password.update');
});


/*
|--------------------------------------------------------------------------
| ログイン済みユーザー専用
|--------------------------------------------------------------------------
|
| 一般ユーザー・管理者の両方が利用できます。
|
*/

// ==================================================
// ログイン済みなら使える
// ※メール未認証でも必要
// ==================================================
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');

    // メール認証待ち画面
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // メール内の認証リンク
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/')
            ->with('success', 'メールアドレスの確認が完了しました。');
    })->middleware('signed')->name('verification.verify');

    // 確認メール再送
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with(
            'success',
            '確認メールを再送しました。'
        );
    })->middleware('throttle:6,1')->name('verification.send');
});


// ==================================================
// メール認証済みのユーザーだけ使える
// ==================================================
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/my-event-participants', [EventParticipantController::class, 'index'])
        ->name('event-participants.index');
    Route::get(
        '/my-event-participants/cancelled',
        [EventParticipantController::class, 'cancelled']
    )->name('event-participants.cancelled');

    Route::post(
        '/events/{event}/participants',
        [EventParticipantController::class, 'store']
    )->name('event-participants.store');

    Route::get(
        '/event-participants/{eventParticipant}/cancel',
        [EventParticipantController::class, 'confirmCancel']
    )->name('event-participants.cancel-confirm');

    Route::delete(
        '/event-participants/{eventParticipant}',
        [EventParticipantController::class, 'destroy']
    )->name('event-participants.destroy');

    Route::post(
        '/event-participants/{eventParticipant}/checkout',
        [CheckoutController::class, 'store']
    )->name('checkout.store');

    Route::get(
        '/checkout/success',
        [CheckoutController::class, 'success']
    )->name('checkout.success');


    // アカウント・プロフィール
    Route::get('/account/edit', [AccountController::class, 'edit'])
        ->name('account.edit');

    Route::put('/account', [AccountController::class, 'update'])
        ->name('account.update');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');


    // イベントチャット
    Route::get(
        '/events/{event}/chat',
        [EventChatController::class, 'show']
    )->name('event-chat.show');

    Route::post(
        '/events/{event}/chat/messages',
        [EventChatController::class, 'storeMessage']
    )->name('event-chat.messages.store');

    Route::post(
        '/messages/{message}/read',
        [EventChatController::class, 'markAsRead']
    )->name('messages.read');

    Route::post(
        '/events/{event}/chat/archive',
        [EventChatController::class, 'archive']
    )->name('event-chat.archive');

    Route::post(
        '/events/{event}/chat/restore',
        [EventChatController::class, 'restore']
    )->name('event-chat.restore');


    // ダイレクトチャット
    Route::post(
        '/direct-chats/{user}',
        [DirectChatController::class, 'start']
    )->name('direct-chat.start');

    Route::get(
        '/direct-chats/{conversation}',
        [DirectChatController::class, 'show']
    )->name('direct-chat.show');

    Route::post(
        '/direct-chats/{conversation}/messages',
        [DirectChatController::class, 'storeMessage']
    )->name('direct-chat.messages.store');


    // チャット一覧
    Route::get('/chats', [ChatController::class, 'index'])
        ->name('chats.index');

    Route::post(
        '/chats/{conversation}/archive',
        [ChatController::class, 'archive']
    )->name('chats.archive');

    Route::get(
        '/chats/archived',
        [ChatController::class, 'archived']
    )->name('chats.archived');

    Route::post(
        '/chats/{conversation}/restore',
        [ChatController::class, 'restore']
    )->name('chats.restore');
    //フィードバック
    Route::get(
        '/feedbacks',
        [FeedbackController::class, 'index']
    )->name('feedback.index');
    Route::get(
        '/feedback',
        [FeedbackController::class, 'create']
    )->name('feedback.create');

    Route::post(
        '/feedback',
        [FeedbackController::class, 'store']
    )->name('feedback.store');


    // stripe
    Route::get(
        '/stripe/connect/guide',
        [StripeConnectController::class, 'guide']
    )->name('stripe.connect.guide');
    Route::get('/stripe/connect', [StripeConnectController::class, 'connect'])
        ->name('stripe.connect.start');

    Route::get('/stripe/connect/refresh', [StripeConnectController::class, 'refresh'])
        ->name('stripe.connect.refresh');

    Route::get('/stripe/connect/return', [StripeConnectController::class, 'return'])
        ->name('stripe.connect.return');

    // 管理者からのお知らせ
    Route::get(
        '/admin-notices',
        [AdminNoticeController::class, 'index']
    )->name('admin-notices.index');

    Route::get(
        '/admin-notices/{adminNotice}',
        [AdminNoticeController::class, 'show']
    )->name('admin-notices.show');

    Route::prefix('organizer')
        ->name('organizer.')
        ->group(function () {

            Route::get('/events', [OrganizerEventController::class, 'index'])
                ->name('events.index');

            Route::get('/events/create', [OrganizerEventController::class, 'create'])
                ->name('events.create');

            Route::post('/events', [OrganizerEventController::class, 'store'])
                ->name('events.store');

            Route::get('/events-archived', [OrganizerEventController::class, 'archived'])
                ->name('events.archived');

            Route::get('/events/{event}', [OrganizerEventController::class, 'show'])
                ->name('events.show');

            Route::get('/events/{event}/edit', [OrganizerEventController::class, 'edit'])
                ->name('events.edit');

            Route::put('/events/{event}', [OrganizerEventController::class, 'update'])
                ->name('events.update');

            Route::delete('/events/{event}', [OrganizerEventController::class, 'destroy'])
                ->name('events.destroy');

            Route::patch('/events/{event}/archive', [OrganizerEventController::class, 'archive'])
                ->name('events.archive');

            Route::patch('/events/{event}/restore-archive', [OrganizerEventController::class, 'restoreArchive'])
                ->name('events.restore-archive');

            Route::patch(
                '/events/{event}/images/{eventImage}/make-primary',
                [OrganizerEventController::class, 'makePrimaryImage']
            )->name('events.images.make-primary');

            Route::delete(
                '/events/{event}/images/{eventImage}',
                [OrganizerEventController::class, 'destroyImage']
            )->name('events.images.destroy');
            Route::patch(
                '/event-participants/{eventParticipant}/attendance',
                [OrganizerEventParticipantController::class, 'updateAttendance']
            )->name('event-participants.attendance');
            Route::view(
                '/event-guidelines',
                'organizer.events.guidelines'
            )->name('events.guidelines');
        });
    // ユーザー通報
    Route::get(
        '/profiles/{user}/report',
        [UserReportController::class, 'create']
    )->name('reports.create');

    Route::post(
        '/profiles/{user}/report',
        [UserReportController::class, 'store']
    )->name('reports.store');
    Route::get(
        '/my-reports/{userReport}',
        [UserReportController::class, 'show']
    )->name('reports.show');

    Route::post(
        '/my-reports/{userReport}/messages',
        [UserReportController::class, 'storeMessage']
    )->name('reports.messages.store');
    Route::get(
        '/my-reports',
        [UserReportController::class, 'index']
    )->name('reports.index');
    Route::get(
        '/report-attachments/{attachment}',
        [ReportMessageAttachmentController::class, 'show']
    )->name('report-attachments.show');
    // ユーザーの警告・アカウント停止のステータス確認
    Route::get(
        '/account/moderation-status',
        [ModerationStatusController::class, 'show']
    )->name('moderation.status');
    // 警告・アカウント停止メッセージ
    Route::get(
        '/moderation-actions/{action}/messages',
        [ModerationMessageController::class, 'show']
    )->name('moderation-messages.show');

    Route::post(
        '/moderation-actions/{action}/messages',
        [ModerationMessageController::class, 'store']
    )->name('moderation-messages.store');
});

/*
|--------------------------------------------------------------------------
| 管理者専用
|--------------------------------------------------------------------------
|
| ログイン済み かつ is_admin = true のユーザーだけ利用できます。
|
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/events', [AdminEventController::class, 'index'])
            ->name('events.index');

        Route::get('/events/create', [AdminEventController::class, 'create'])
            ->name('events.create');

        Route::post('/events', [AdminEventController::class, 'store'])
            ->name('events.store');

        Route::get('/events/{event}', [AdminEventController::class, 'show'])
            ->name('events.show');

        Route::get('/events/{event}/edit', [AdminEventController::class, 'edit'])
            ->name('events.edit');

        Route::put('/events/{event}', [AdminEventController::class, 'update'])
            ->name('events.update');

        Route::delete('/events/{event}', [AdminEventController::class, 'destroy'])
            ->name('events.destroy');
        Route::patch(
            '/event-participants/{eventParticipant}/attendance',
            [AdminEventParticipantController::class, 'updateAttendance']
        )->name('event-participants.attendance');

        Route::delete(
            '/events/{event}/images/{eventImage}',
            [AdminEventController::class, 'destroyImage']
        )->name('events.images.destroy');
        Route::patch(
            '/events/{event}/images/{eventImage}/make-primary',
            [AdminEventController::class, 'makePrimaryImage']
        )->name('events.images.make-primary');
        Route::get('/maintenance', [MaintenanceController::class, 'edit'])
            ->name('maintenance.edit');

        Route::patch('/maintenance', [MaintenanceController::class, 'update'])
            ->name('maintenance.update');
        Route::get(
            '/reports',
            [AdminUserReportController::class, 'index']
        )->name('reports.index');

        Route::get(
            '/reports/{userReport}',
            [AdminUserReportController::class, 'show']
        )->name('reports.show');
        Route::patch(
            '/reports/{userReport}/status',
            [AdminUserReportController::class, 'updateStatus']
        )->name('reports.update-status');
        Route::post(
            '/reports/{userReport}/messages',
            [AdminUserReportController::class, 'storeMessage']
        )->name('reports.messages.store');
        // ユーザーの警告・アカウント停止
        Route::post(
            '/users/{user}/warning',
            [UserModerationController::class, 'warn']
        )->name('users.warning');

        Route::post(
            '/users/{user}/suspend',
            [UserModerationController::class, 'suspend']
        )->name('users.suspend');
        Route::get(
            '/users/{user}/moderation',
            [UserModerationController::class, 'edit']
        )->name('users.moderation.edit');
        Route::post(
            '/users/{user}/moderation/clear',
            [UserModerationController::class, 'clearModeration']
        )->name('users.moderation.clear');
        Route::get(
            '/moderation',
            [UserModerationController::class, 'index']
        )->name('moderation.index');
        Route::post(
            '/moderation/{action}/messages',
            [UserModerationController::class, 'storeMessage']
        )->name('moderation.messages.store');
        // フィードバック
        Route::get(
            '/feedbacks',
            [\App\Http\Controllers\Admin\FeedbackController::class, 'index']
        )->name('feedbacks.index');

        Route::get(
            '/feedbacks/{feedback}',
            [\App\Http\Controllers\Admin\FeedbackController::class, 'show']
        )->name('feedbacks.show');

    });