<?php

namespace App\Models;

use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// requester_id, offer_status_id, counteroffer_made and the dates are set by our code, never by the request.
#[Fillable(['type', 'book_requested_id', 'book_offered_id', 'message'])]
class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'counteroffer_made' => 'boolean',
            'owner_confirmed_at' => 'datetime',
            'requester_confirmed_at' => 'datetime',
            'owner_last_read_at' => 'datetime',
            'requester_last_read_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requestedBook(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_requested_id');
    }

    public function offeredBook(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_offered_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OfferStatus::class, 'offer_status_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
