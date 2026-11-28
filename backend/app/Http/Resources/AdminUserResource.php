<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * A user as the admin panel sees it (AdminUser in the API contract).
 * Used by suspend/reactivate (#36) and by the admin user list and detail (#34).
 *
 * @mixin User
 */
class AdminUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $averageRating = $this->ratingsReceived()->avg('score');
        // Si la lista ya trae el dato, sin consulta extra - Laurence
        $loaded = $this->resource->getAttributes();
        $averageRating = array_key_exists('ratings_received_avg_score', $loaded)
            ? $loaded['ratings_received_avg_score']
            : $this->ratingsReceived()->avg('score');
        $booksCount = array_key_exists('books_count', $loaded)
            ? (int) $loaded['books_count']
            : $this->books()->count();
        // The photos disk comes from config (public locally, a bucket in production)
        /** @var FilesystemAdapter $photosDisk */
        $photosDisk = Storage::disk(config('filesystems.photos_disk'));

        // The comments on each key become the field descriptions in Scramble (in Spanish, like the contract)
        return [
            /** @example 3 */
            'id' => $this->id,
            /** @example Ane */
            'username' => $this->username,
            /**
             * @var string|null
             *
             * @example https://example.com/storage/profile-photos/foto.jpg
             */
            // Only the path is stored (photo_path); the URL is built from the photos disk
            'photo_url' => $this->photo_path ? $photosDisk->url($this->photo_path) : null,
            /** @example ane@example.com */
            'email' => $this->email,
            /**
             * Estado de la cuenta.
             *
             * @var 'active'|'suspended'|'deleted'
             */
            'status' => $this->status,
            /**
             * Corresponde a `profiles.code`.
             *
             * @var 'user'|'admin'
             */
            'role' => $this->profile->code,
            /** Fecha de alta. */
            'created_at' => $this->created_at,
            // 'books_count' => $this->books()->count(),
            /** Libros publicados, en cualquier estado. */
            'books_count' => $booksCount,
            /** Media de las valoraciones recibidas (1 decimal), o `null` si no tiene ninguna. */
            'average_rating' => $averageRating === null ? null : round((float) $averageRating, 1),
            /** Avisos acumulados por denuncias (0 a 3). */
            'strikes' => $this->strikes,
        ];
    }
}
