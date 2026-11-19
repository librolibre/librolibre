<?php

namespace Tests\Feature\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_is_stored_and_its_url_returned(): void
    {}

    public function test_photo_path_is_saved_on_the_user(): void
    {}

    public function test_a_file_that_is_not_an_image_returns_422(): void
    {}

    public function test_photo_larger_than_2_mb_returns_422(): void
    {}

    public function test_new_photo_replaces_the_old_file(): void
    {}

    public function test_photo_requires_a_token(): void
    {}
}