<?php

namespace App\Models;

use Database\Factories\UserReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// reporter_user_id, status, strike_added and the resolution fields are set by our code.
#[Fillable([
    'reported_user_id',
    'offer_id',
    'reason_id',
    'description'
    ])]
class UserReport extends Model
{
    /** @use HasFactory<UserReportFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'strike_added' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reported(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function resolver(): BelongsTo           // Who solved it?
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReportReason::class, 'reason_id');
    }
}
