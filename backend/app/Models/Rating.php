<?php

namespace App\Models;

use Database\Factories\RatingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// offer_id, user_rater_id and user_rated_id are set by our code, never by the request.
#[Fillable(['score', 'comment'])]
class Rating extends Model
{
    /** @use HasFactory<RatingFactory> */
    use HasFactory;

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_rater_id');
    }

    public function rated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_rated_id');
    }
}
