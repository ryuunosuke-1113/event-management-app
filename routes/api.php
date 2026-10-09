<?php

use App\Models\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

Route::post('/login', function (Request $request) {
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = \App\Models\User::where('email', $validated['email'])->first();

    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'message' => 'メールアドレスまたはパスワードが正しくありません。',
        ], 422);
    }

    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
    ]);
});

Route::get('/events', function (Request $request) {
    $query = Event::query()
        ->with('images')
        ->where('status', 'published');

    if ($request->filled('keyword')) {
        $query->where(
            'title',
            'like',
            '%' . $request->keyword . '%'
        );
    }

    if ($request->filled('event_date')) {
        $query->whereDate(
            'event_date',
            $request->event_date
        );
    }

    return $query
        ->orderBy('event_date')
        ->get()
        ->map(function (Event $event) {
            $mainImage = $event->images->first();

            return [
                'id' => $event->id,
                'title' => $event->title,
                'event_date' => $event->event_date,
                'place' => $event->place,
                'price' => $event->price,
                'capacity' => $event->capacity,
                'image_url' => $mainImage
                    ? asset('storage/' . $mainImage->image_path)
                    : null,
            ];
        });
});
Route::get('/events/{event}', function (Event $event) {
    abort_unless($event->status === 'published', 404);

    $event->load([
        'organizer:id,name',
        'images',
    ]);

    $occupiedCount = $event->participants()
        ->where('status', 'confirmed')
        ->count();

    $isFull = $occupiedCount >= $event->capacity;

    return response()->json([
        'id' => $event->id,
        'title' => $event->title,
        'event_date' => $event->event_date,
        'place' => $event->place,
        'price' => $event->price,
        'capacity' => $event->capacity,
        'occupied_count' => $occupiedCount,
        'is_full' => $isFull,
        'description' => $event->description,

        'images' => $event->images
            ->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => asset('storage/' . $image->image_path),
                ];
            })
            ->values(),

        'organizer' => $event->organizer
            ? [
                'id' => $event->organizer->id,
                'name' => $event->organizer->name,
            ]
            : null,
        'cancel_policy' => [
            'three_days_before' => (int) config(
                'event.cancel_policy.participant.three_days_before'
            ),
            'two_days_to_day_before' => (int) config(
                'event.cancel_policy.participant.two_days_to_day_before'
            ),
            'event_day' => (int) config(
                'event.cancel_policy.participant.event_day'
            ),
            'organizer_cancelled' => (int) config(
                'event.cancel_policy.organizer_cancelled'
            ),
        ],
    ]);
})->whereNumber('event');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'email' => $request->user()->email,
    ]);
});
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'ログアウトしました。',
    ]);
});