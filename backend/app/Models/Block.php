<?php

namespace App\Models;

use Database\Factories\BlockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// user_blocker_id is the logged-in user, set by our code.
#[Fillable(['user_blocked_id'])]
class Block extends Model
{
    /** @use HasFactory<BlockFactory> */
    use HasFactory;

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_blocker_id');
    }

    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_blocked_id');
    }
}
