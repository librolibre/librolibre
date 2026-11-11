<?php

namespace Tests\Feature\Models;

use App\Models\Profile;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Smoke test: the migration and the factory work together
    // ------------------------------------------------------------------

    /** A new user gets the default values the business rules expect. */
    public function test_factory_creates_a_user_with_default_values(): void
    {
        $user = User::factory()->create()->fresh();

        $this->assertSame('active', $user->status);
        $this->assertSame(0, $user->strikes);
        $this->assertNull($user->privacy_accepted_at);
        $this->assertSame('user', $user->profile->code);
    }

    // ------------------------------------------------------------------
    // Constraints
    // ------------------------------------------------------------------

    /** google_id is how we recognise a returning Google account. */
    public function test_google_id_must_be_unique(): void
    {
        User::factory()->create(['google_id' => '123456789012345678901']);

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create(['google_id' => '123456789012345678901']);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'ane@example.com']);

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create(['email' => 'ane@example.com']);
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'ane']);

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create(['username' => 'ane']);
    }

    /** Every user must have a profile (user or admin). */
    public function test_profile_is_required(): void
    {
        $this->expectException(QueryException::class);

        User::factory()->create(['profile_id' => null]);
    }

    /** The foreign key rejects a profile that does not exist. */
    public function test_profile_must_exist(): void
    {
        $this->expectException(QueryException::class);

        User::factory()->create(['profile_id' => 999]);
    }

    /** A profile that still has users cannot be deleted. */
    public function test_profile_in_use_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $this->expectException(QueryException::class);

        $user->profile->delete();
    }

    /** Spanish postal codes have 5 characters (and may start with 0). */
    public function test_postal_code_keeps_leading_zero_and_rejects_longer_values(): void
    {
        $user = User::factory()->create(['postal_code' => '01005']);
        $this->assertSame('01005', $user->fresh()->postal_code);

        $this->expectException(QueryException::class);

        User::factory()->create(['postal_code' => '010050']);
    }

    // ------------------------------------------------------------------
    // Privacy and security rules
    // ------------------------------------------------------------------

    /** Location is stored rounded to ~1 km, even if the code forgets to round it. */
    public function test_coordinates_are_stored_with_two_decimals(): void
    {
        $user = User::factory()->create([
            'latitude' => 42.846718,
            'longitude' => -2.672990,
        ])->fresh();

        $this->assertSame('42.85', $user->latitude);
        $this->assertSame('-2.67', $user->longitude);
    }

    /** status, strikes and profile_id can only be changed by our own code, never by request data. */
    public function test_moderation_fields_cannot_be_mass_assigned(): void
    {
        Profile::create(['code' => 'admin', 'name' => 'Administrador']);
        $user = User::factory()->create();
        $userProfileId = $user->profile_id;

        $user->fill([
            'status' => 'suspended',
            'strikes' => 3,
            'profile_id' => Profile::where('code', 'admin')->value('id'),
        ])->save();

        $user->refresh();
        $this->assertSame('active', $user->status);
        $this->assertSame(0, $user->strikes);
        $this->assertSame($userProfileId, $user->profile_id);
    }

    /** google_id is internal: it never appears in JSON responses. */
    public function test_google_id_is_hidden_from_json(): void
    {
        $user = User::factory()->create();

        $this->assertArrayNotHasKey('google_id', $user->toArray());
    }

    /** privacy_accepted_at is a date, so we know when consent was given. */
    public function test_privacy_accepted_at_is_cast_to_a_date(): void
    {
        $user = User::factory()->create(['privacy_accepted_at' => now()])->fresh();

        $this->assertInstanceOf(CarbonInterface::class, $user->privacy_accepted_at);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function test_user_belongs_to_a_profile(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Profile::class, $user->profile);
        $this->assertSame($user->profile_id, $user->profile->id);
    }
}
