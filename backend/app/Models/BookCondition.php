<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name'])]
class BookCondition extends Model
{
    // The column on books is `condition_id`, not `book_condition_id`, so it is given explicitly.
    // public function books(): HasMany
    // {
    //     return $this->hasMany(Book::class, 'condition_id');
    // }
}
