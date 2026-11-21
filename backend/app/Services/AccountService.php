<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Block;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * An account's lifecycle: delete it now (#29); suspend and reactivate it later (#36).
 * The steps they share (revoking tokens, later cancelling offers) live here once, as private methods.
 * Laravel injects it into the controllers by type-hint (no constructor needed).
 */
class AccountService
{
    /**
     * Anonymises the account instead of removing the row, because other users' exchanges,
     * messages and ratings still point to it (#29, RN-16).
     */
    public function delete(User $user): void
    {
        // 1. Remember the photo path before anything changes (anonymise() clears it)
        $photoPath = $user->photo_path;

        // 2. Inside DB::transaction: anonymise, withdraw books, remove blocks, revoke tokens
        DB::transaction(function () use ($user) {
            $this->anonymise($user);
            $this->withdrawBooks($user);
            $this->removeBlocks($user);
            $this->revokeTokens($user);
        });

        // 3. After the transaction: delete the photo file from the photos disk
        $disk = config('filesystems.photos_disk');

        if ($photoPath !== null) {
            Storage::disk($disk)->delete($photoPath);
        }
    }

    // TODO (#36) reactivate(User $user)

    /**
     * Suspends the account (#36, RN-17): the user can no longer log in or use the API.
     * The admin and the reason are recorded in admin_actions.
     *
     * @param  User  $user  the user being suspended (from the URL)
     * @param  User  $admin  the admin doing it (the logged-in user)
     *
     * @throws BusinessRuleException ADMIN_PROTECTED when the target is the admin themselves or another admin (RN-19)
     */
    public function suspend(User $user, User $admin, string $reason): void
    {
        // The rules are checked before anything is saved: a throw stops the method here,
        // and BusinessRuleException::render() answers 409 with the code
        // is(): same model (same table and id). Checked first so the message is the specific one
        if ($user->is($admin)) {
            throw new BusinessRuleException('ADMIN_PROTECTED', 'No puedes suspender tu propia cuenta.');
        }

        if ($user->isAdmin()) {
            throw new BusinessRuleException('ADMIN_PROTECTED', 'No se puede suspender a un administrador.');
        }

        if ($user->status !== 'active') {
            throw new BusinessRuleException('INVALID_STATUS', 'Esta cuenta no está activa.');
        }

        DB::transaction(function () use ($user, $admin, $reason) {
            // The account is suspended
            $user->status = 'suspended';
            $user->save();

            // All their sessions end: their next request gets 401
            $this->revokeTokens($user);

            // Who suspended and why (admin_id is from adminActions() relation)
            $admin->adminActions()->create([
                'action' => 'suspend_user',
                'target_type' => 'user',
                'target_id' => $user->id,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Replaces email, google_id and username with values that identify nobody and stay unique,
     * clears the personal fields and the languages, and sets the status to deleted.
     */
    private function anonymise(User $user): void
    {
        $user->status = 'deleted';
        $user->google_id = 'deleted-'.$user->id;
        $user->email = 'deleted-'.$user->id.'@librolibre.invalid';
        $user->username = 'usuario-eliminado-'.$user->id;

        $nullFields = [
            'description', 'postal_code', 'latitude',
            'longitude', 'photo_path',
        ];

        foreach ($nullFields as $field) {
            $user->{$field} = null;
        }

        $user->languages()->detach();
        $user->save();
    }

    /** Every book except the delivered and the already withdrawn ones becomes withdrawn. */
    private function withdrawBooks(User $user): void
    {
        $user->books()
            ->whereNotIn('status', ['delivered', 'withdrawn'])
            ->update(['status' => 'withdrawn']);
    }

    /** Removes the blocks in both directions: the ones the user made and the ones against them. */
    private function removeBlocks(User $user): void
    {
        $user->blockedUsers()->detach();
        Block::where('user_blocked_id', $user->id)->delete();
    }

    /** Deletes all the user's tokens, not only the current one (logout only deletes that one). */
    private function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
