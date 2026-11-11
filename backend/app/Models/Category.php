<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'name'])]
class Category extends Model
{
    // public function book(): BelongsToMany
    // {
    //     return $this->belongsToMany(Book::class);
    // }
}
