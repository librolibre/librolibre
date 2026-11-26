<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'username',
    'email',
    'google_id',
    'photo_path',
    'description',
    'postal_code',
    'latitude',
    'longitude',
    'privacy_accepted_at',
])]
#[Hidden(['google_id'])]
class User extends Authenticatable
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
            'privacy_accepted_at' => 'datetime',
            'latitude' => 'decimal:2',
            'longitude' => 'decimal:2',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    // An admin is a user whose profile has the code "admin" (RN-19).
    // The only place this rule lives: the admin Gate and the suspension rules use it.
    public function isAdmin(): bool
    {
        return $this->profile?->code === 'admin';
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class);
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    // Offers this user has sent
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'requester_id');
    }

    // Users this user has blocked
    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blocks', 'user_blocker_id', 'user_blocked_id')->withTimestamps();
    }

    // Ratings other users have given to this user
    public function ratingsReceived(): HasMany
    {
        return $this->hasMany(Rating::class, 'user_rated_id');
    }

    // Actions this admin has taken in the admin panel (admin_actions.admin_id)
    public function adminActions(): HasMany
    {
        return $this->hasMany(AdminAction::class, 'admin_id');
    }

    // Acciones de administración sobre esta cuenta - Laurence
    public function actionsReceived(): HasMany
    {
        return $this->hasMany(AdminAction::class, 'target_id')
            ->where('target_type', 'user');
    }

    // Completed exchanges, as requester or as owner of the requested book
    public function completedExchangesCount(): int
    {
        return Offer::whereHas('status', fn ($query) => $query->where('code', 'completed'))
            ->where(fn ($query) => $query
                ->where('requester_id', $this->id)
                ->orWhereHas('requestedBook', fn ($book) => $book->where('user_id', $this->id)))
            ->count();
    }
}
