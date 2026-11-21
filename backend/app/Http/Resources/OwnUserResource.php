<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

/**
 * The logged-in user's own profile (OwnUser in the API contract).
 * status, strikes, profile_id and the exact columns stay internal.
 *
 * @mixin User
 */
class OwnUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $averageRating = $this->ratingsReceived()->avg('score');
        // The photos disk comes from config (public locally, a bucket in production)
        /** @var FilesystemAdapter $photosDisk */
        $photosDisk = Storage::disk(config('filesystems.photos_disk'));

        // The comments on each key become the field descriptions in Scramble (in Spanish, like the contract)
        return [
            'id' => $this->id,
            'username' => $this->username,
            /** @var string|null */
            // 'photo_url' => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
            'photo_url' => $this->photo_path ? $photosDisk->url($this->photo_path) : null,
            'description' => $this->description,
            /**
             * Códigos de los idiomas de lectura.
             * @var list<string>
             * @example ["es", "eu"]
             */
            'languages' => $this->languages->pluck('code'),
            'average_rating' => $averageRating === null ? null : round((float) $averageRating, 1),
            'ratings_count' => $this->ratingsReceived()->count(),
            /** Intercambios completados, como solicitante o como dueño del libro. */
            'exchanges_count' => $this->completedExchangesCount(),
            'member_since' => $this->created_at,
            'email' => $this->email,
            /** Coordenadas redondeadas a ≈ 1 km. */
            'location' => $this->latitude === null || $this->longitude === null ? null : [
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ],
            /**
             * Corresponde a `profiles.code`.
             * @var 'user'|'admin'
             */
            'role' => $this->profile->code,
            /** Si ya aceptó la política de privacidad. */
            'privacy_accepted' => $this->privacy_accepted_at !== null,
            'postal_code' => $this->postal_code,
        ];
    }
}
