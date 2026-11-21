<?php

namespace Tests\Feature\Api\Profile;

use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    /** Fake version of the configured photos disk: nothing is written to the real disk or bucket. */
    private FilesystemAdapter $photosDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->photosDisk = Storage::fake(config('filesystems.photos_disk'));
    }

    /** The photo is stored in profile-photos/ and the response has its URL. */
    public function test_photo_is_stored_and_its_url_returned(): void
    {
        // Arrange
        $user = User::factory()->create();
        $photo = UploadedFile::fake()->image('foto.jpg');

        // Act
        $response = $this->withToken($user->createToken('test')->plainTextToken)->postJson('/api/me/photo', [
            'photo' => $photo,
        ]);

        // Assert
        $path = 'profile-photos/'.$photo->hashName();

        $response->assertOk();
        $this->photosDisk->assertExists($path);
        $response->assertJsonPath('data.photo_url', $this->photosDisk->url($path));
    }

    /** Only the path is saved on the user (the URL is built from it by the resource). */
    public function test_photo_path_is_saved_on_the_user(): void
    {
        $user = User::factory()->create();
        $photo = UploadedFile::fake()->image('foto.png');

        $this->withToken($user->createToken('test')->plainTextToken)->postJson('/api/me/photo', [
            'photo' => $photo,
        ])->assertOk();

        $this->assertSame('profile-photos/'.$photo->hashName(), $user->fresh()->photo_path);
    }

    /** A file that is not a JPG or PNG image is rejected, and nothing is stored. */
    public function test_a_file_that_is_not_an_image_returns_422(): void
    {
        $user = User::factory()->create();
        $document = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->withToken($user->createToken('test')->plainTextToken)->postJson('/api/me/photo', [
            'photo' => $document,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('photo');
        $this->assertSame([], $this->photosDisk->allFiles());
        $this->assertNull($user->fresh()->photo_path);
    }

    /** The limit is 2 MB (2048 KB): one KB more is rejected. */
    public function test_photo_larger_than_2_mb_returns_422(): void
    {
        $user = User::factory()->create();
        $photo = UploadedFile::fake()->image('grande.jpg')->size(2049);

        $response = $this->withToken($user->createToken('test')->plainTextToken)->postJson('/api/me/photo', [
            'photo' => $photo,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('photo');
        $this->assertSame([], $this->photosDisk->allFiles());
    }

    /** Uploading a new photo deletes the old file, so old photos do not pile up. */
    public function test_new_photo_replaces_the_old_file(): void
    {
        // Arrange: a user who already has a photo
        $this->photosDisk->put('profile-photos/old.jpg', 'old photo');
        $user = User::factory()->create(['photo_path' => 'profile-photos/old.jpg']);
        $photo = UploadedFile::fake()->image('nueva.jpg');

        // Act
        $this->withToken($user->createToken('test')->plainTextToken)->postJson('/api/me/photo', [
            'photo' => $photo,
        ])->assertOk();

        // Assert
        $newPath = 'profile-photos/'.$photo->hashName();

        $this->photosDisk->assertMissing('profile-photos/old.jpg');
        $this->photosDisk->assertExists($newPath);
        $this->assertSame($newPath, $user->fresh()->photo_path);
    }

    public function test_photo_requires_a_token(): void
    {
        $this->postJson('/api/me/photo', [
            'photo' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertUnauthorized();
    }
}
