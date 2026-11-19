<?php

namespace Tests\Feature\Api\Profile;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // PATCH /me: basic fields
    // ------------------------------------------------------------------

    public function test_update_returns_own_user_shape(): void
    {}

    public function test_update_only_changes_the_fields_sent(): void
    {}

    public function test_username_must_be_between_2_and_60_characters(): void
    {}

    public function test_description_cannot_be_longer_than_300_characters(): void
    {}

    public function test_postal_code_must_have_5_digits(): void
    {}

    public function test_postal_code_keeps_the_leading_zero(): void
    {}

    public function test_status_strikes_and_role_cannot_be_changed(): void
    {}

    public function test_update_requires_a_token(): void
    {}

    // ------------------------------------------------------------------
    // PATCH /me: reading languages
    // ------------------------------------------------------------------

    public function test_languages_with_an_unknown_code_return_422(): void
    {}

    public function test_languages_replace_the_previous_list(): void
    {}
}