<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// user_id and status are not fillable: they are set by our code, never by the request.
#[Fillable([
    'title',
    'author',
    'isbn',
    'language_id',
    'publisher',
    'publication_year',
    'publication_place',
    'page_count',
    'cover_url',
    'binding_type_id',
    'condition_id',
    'dimensions',
    'weight',
    'edition_name',
    'notes',
    'is_donation',
])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_donation' => 'boolean',
            'weight' => 'decimal:2',
            'publication_year' => 'integer',
            'page_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function bindingType(): BelongsTo
    {
        return $this->belongsTo(BindingType::class);
    }

    // Column is condition_id, not book_condition_id
    public function condition(): BelongsTo
    {
        return $this->belongsTo(BookCondition::class, 'condition_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function defects(): BelongsToMany
    {
        return $this->belongsToMany(Defect::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(BookPhoto::class)->orderBy('position');
    }
}
