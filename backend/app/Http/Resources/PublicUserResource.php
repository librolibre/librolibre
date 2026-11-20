<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Another user's public profile (PublicUser in the API contract).
 * Never includes email, postal code or coordinates: those are only in OwnUserResource.
 *
 * @mixin User
 */
class PublicUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $averageRating = $this->ratingsReceived()->avg('score');
        // The logged-in user looking at this profile, or null for a visitor (auth.optional)
        $viewer = $request->user();

        // The comments on each key become the field descriptions in Scramble (in Spanish, like the contract)
        return [
            'id' => $this->id,
            'username' => $this->username,
            /** @var string|null */
            'photo_url' => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
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
            /**
             * Solo si hay sesión iniciada: `true` si yo he bloqueado a este usuario.
             * @var bool
             */
            // when(): for a visitor the key is left out of the JSON; the query only runs for a logged-in user
            'blocked_by_me' => $this->when(
                $viewer !== null,
                fn () => $viewer->blockedUsers()->whereKey($this->id)->exists(),
            ),
        ];
    }
}
