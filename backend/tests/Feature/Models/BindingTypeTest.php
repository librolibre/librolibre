<?php

namespace Tests\Feature\Models;

use App\Models\BindingType;
use Database\Seeders\BindingTypeSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BindingTypeTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder creates every binding type (same codes as the API contract). */
    public function test_seeder_creates_all_expected_binding_types(): void
    {
        $this->seed(BindingTypeSeeder::class);
        $expected = [
            'paperback', 'hardcover', 'pocket', 'other',
            ];
        $this->assertEqualsCanonicalizing($expected, BindingType::pluck('code')->all());
    }

    /** Running the seeder again (for example on Neon) must not duplicate rows. */
    public function test_binding_type_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(BindingTypeSeeder::class);
        $countFirstRun = BindingType::count();

        $this->seed(BindingTypeSeeder::class);

        $this->assertSame($countFirstRun, BindingType::count());
    }

    /** `code` is what the code compares against, so it can never repeat. */
    public function test_binding_type_code_must_be_unique(): void
    {
        BindingType::create(['code' => 'paperback', 'name' => 'Tapa blanda']);

        $this->expectException(UniqueConstraintViolationException::class);

        BindingType::create(['code' => 'paperback', 'name' => 'Tapita']);
    }

    /** Two binding types with the same name would look identical in the dropdown. */
    public function test_binding_type_name_must_be_unique(): void
    {
        BindingType::create(['code' => 'paperback', 'name' => 'Tapa blanda']);

        $this->expectException(UniqueConstraintViolationException::class);

        BindingType::create(['code' => 'paperback2', 'name' => 'Tapa blanda']);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    // public function test_binding_type_has_many_books(): void
    // {
    //     $bindingType = BindingType::create(['code' => 'paperback', 'name' => 'Tapa blanda']);
    //     $book = Book::factory()->create(['binding_type_id' => $bindingType->id]);

    //     $this->assertTrue($bindingType->books->contains($book));
    // }
}
