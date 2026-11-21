<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'requires_description'])]
class ReportReason extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_description' => 'boolean',
        ];
    }

    // The column on user_reports is `reason_id`, not `report_reason_id`, so it is given explicitly.
    public function userReports(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reason_id');
    }
}
