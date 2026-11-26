<?php

namespace App\Http\Resources;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A book (Book in the API contract), with the fields stored in the books table.
 *
 * @mixin Book
 */
class BookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example 42 */
            'id' => $this->id,
            /** Vacío si el libro se añadió a mano (RF-08). */
            'isbn' => $this->isbn,
            /** @example Obabakoak */
            'title' => $this->title,
            /** @example Bernardo Atxaga */
            'author' => $this->author,
            'publisher' => $this->publisher,
            'publication_year' => $this->publication_year,
            'publication_place' => $this->publication_place,
            'edition_name' => $this->edition_name,
            'page_count' => $this->page_count,
            /** @example 21 x 14 cm */
            'dimensions' => $this->dimensions,
            /**
             * En gramos.
             *
             * @var float|null
             */
            'weight' => $this->weight === null ? null : (float) $this->weight,
            /** Portada de Open Library, o `null` si no hay. */
            'cover_url' => $this->cover_url,
            /** `true`: trueque y donación; `false`: solo trueque. */
            'is_donation' => $this->is_donation,
            'notes' => $this->notes,
            /** @var 'available'|'locked'|'reserved'|'delivered'|'paused'|'hidden'|'withdrawn' */
            'status' => $this->status,
            /** Fecha de alta. */
            'created_at' => $this->created_at,
        ];
    }
}
