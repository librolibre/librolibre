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
class AdminUserDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var FilesystemAdapter $photosDisk */
        $photosDisk = Storage::disk(config('filesystems.photos_disk'));

        $photoUrl = fn (?string $path): ?string => $path ? $photosDisk->url($path) : null;

        $averageRating = $this->ratingsReceived()->avg('score');

        return [
            /** @example 3 */
            'id' => $this->id,
            /** @example Ane */
            'username' => $this->username,
            /**
             * @var string|null
             * @example https://example.com/storage/profile-photos/foto.jpg
             */
            'photo_url' => $photoUrl($this->photo_path),
            /** @example ane@example.com */
            'email' => $this->email,
            /**
             * Estado de la cuenta.
             * @var 'active'|'suspended'|'deleted'
             */
            'status' => $this->status,
            /**
             * Corresponde a `profiles.code`.
             * @var 'user'|'admin'
             */
            'role' => $this->profile->code,
            /** Fecha de alta. */
            'created_at' => $this->created_at,
            /** Libros publicados, en cualquier estado. */
            'books_count' => $this->books_count ?? $this->books()->count(),
            /** Media de las valoraciones recibidas (1 decimal), o `null` si no tiene ninguna. */
            'average_rating' => $averageRating === null ? null : round((float) $averageRating, 1),
            /** Avisos acumulados por denuncias (0 a 3). */
            'strikes' => $this->strikes,

            /** Libros del usuario. */
            'books' => $this->whenLoaded('books', fn () => $this->books->map(
                fn ($book) => $this->transformBook($book, $photosDisk, $request)
            )->values()),

            /** Ofertas en las que participa el usuario. */
            'offers' => $this->whenLoaded('offers', fn () => $this->offers->map(fn ($offer) => [
                'id' => $offer->id,
                'type' => $offer->type,
                'status' => $offer->status,
                'requested_book' => $offer->requestedBook
                    ? $this->transformBook($offer->requestedBook, $photosDisk, $request)
                    : null,
                'offered_book' => $offer->offeredBook
                    ? $this->transformBook($offer->offeredBook, $photosDisk, $request)
                    : null,
                'requester' => $offer->requester ? [
                    'id' => $offer->requester->id,
                    'username' => $offer->requester->username,
                    'photo_url' => $photoUrl($offer->requester->photo_path),
                ] : null,
                'owner' => $offer->owner ? [
                    'id' => $offer->owner->id,
                    'username' => $offer->owner->username,
                    'photo_url' => $photoUrl($offer->owner->photo_path),
                ] : null,
                'message' => $offer->message,
                'counteroffer_made' => (bool) ($offer->counteroffer_made ?? false),
                'delivery_confirmed_by_me' => (bool) ($offer->delivery_confirmed_by_me ?? false),
                'delivery_confirmed_by_other' => (bool) ($offer->delivery_confirmed_by_other ?? false),
                'chat_read_only' => (bool) ($offer->chat_read_only ?? false),
                'unread_messages' => (int) ($offer->unread_messages ?? 0),
                'can_rate' => (bool) ($offer->can_rate ?? false),
                'expires_at' => $offer->expires_at,
                'created_at' => $offer->created_at,
                'updated_at' => $offer->updated_at,
            ])->values()),

            /** Valoraciones recibidas por el usuario. */
            'ratings' => $this->whenLoaded('ratingsReceived', fn () => $this->ratingsReceived->map(fn ($rating) => [
                'id' => $rating->id,
                'offer_id' => $rating->offer_id,
                'rater' => $rating->rater ? [
                    'id' => $rating->rater->id,
                    'username' => $rating->rater->username,
                    'photo_url' => $photoUrl($rating->rater->photo_path),
                ] : null,
                'score' => $rating->score,
                'comment' => $rating->comment,
                'is_published' => (bool) ($rating->is_published ?? true),
                'created_at' => $rating->created_at,
            ])->values()),

            /** Denuncias recibidas por el usuario. */
            'reports_received' => $this->whenLoaded('reportsReceived', fn () => $this->reportsReceived->map(fn ($report) => [
                'id' => $report->id,
                'reporter' => $report->reporter ? [
                    'id' => $report->reporter->id,
                    'username' => $report->reporter->username,
                    'photo_url' => $photoUrl($report->reporter->photo_path),
                ] : null,
                'reported_user' => $report->reportedUser
                    ? new AdminUserResource($report->reportedUser)
                    : null,
                'reason' => $report->reason,
                'description' => $report->description,
                'offer_id' => $report->offer_id,
                'status' => $report->status,
                'resolution' => $report->resolution ? [
                    'admin' => $report->resolution->admin ? [
                        'id' => $report->resolution->admin->id,
                        'username' => $report->resolution->admin->username,
                        'photo_url' => $photoUrl($report->resolution->admin->photo_path),
                    ] : null,
                    'reason' => $report->resolution->reason,
                    'strike_added' => (bool) ($report->resolution->strike_added ?? false),
                    'resolved_at' => $report->resolution->resolved_at,
                ] : null,
                'created_at' => $report->created_at,
            ])->values()),

            /** Acciones de moderación aplicadas sobre el usuario. */
            'actions' => $this->whenLoaded('adminActions', fn () => $this->adminActions->map(fn ($action) => [
                'action' => $action->action,
                'admin' => $action->admin ? [
                    'id' => $action->admin->id,
                    'username' => $action->admin->username,
                    'photo_url' => $photoUrl($action->admin->photo_path),
                ] : null,
                'reason' => $action->reason,
                'created_at' => $action->created_at,
            ])->values()),
        ];
    }
}
