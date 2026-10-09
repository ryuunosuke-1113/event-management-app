<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailNotification;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'stripe_account_id', 'account_status',])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
    public function conversationMemberships(): HasMany
    {
        return $this->hasMany(ConversationMember::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
    public function moderationActions(): HasMany
    {
        return $this->hasMany(UserModerationAction::class);
    }
    public function activeWarnings(): HasMany
    {
        return $this->hasMany(UserModerationAction::class)
            ->where('action_type', 'warning')
            ->where('is_active', true);
    }
    public function adminNotices(): HasMany
    {
        return $this->hasMany(AdminNotice::class);
    }
}