<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name'])]
class OfferStatus extends Model
{
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}
