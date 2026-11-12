<?php

namespace Tests\Feature\Models;

use App\Models\Defect;
use Database\Seeders\DefectSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefectTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder creates every defect a user can mark on a book (same codes as the API contract). */
    public function test_seeder_creates_all_expected_defects(): void
    {
        $this->seed(DefectSeeder::class);
        $expected = [
            'underlining', 'margin_notes', 'doodles', 'name_or_dedication', 'damaged_cover',
            'damaged_spine', 'folded_pages', 'loose_pages', 'water_damage', 'yellowed_pages',
            'odor', 'child_use', 'missing_material', 'library_stamp',
            ];
        $this->assertEqualsCanonicalizing($expected, Defect::pluck('code')->all());
    }

    /** Running the seeder again (for example on Neon) must not duplicate rows. */
    public function test_defect_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(DefectSeeder::class);
        $countFirstRun = Defect::count();

        $this->seed(DefectSeeder::class);

        $this->assertSame($countFirstRun, Defect::count());
    }

    /** `code` is what the code compares against, so it can never repeat. */
    public function test_defect_code_must_be_unique(): void
    {
        Defect::create(['code' => 'underlining', 'name' => 'Subrayado o resaltado']);

        $this->expectException(UniqueConstraintViolationException::class);

        Defect::create(['code' => 'underlining', 'name' => 'Subsaltado o rerayado']);
    }

    /** Two defects with the same name would look identical in the dropdown. */
    public function test_defect_name_must_be_unique(): void
    {
        Defect::create(['code' => 'underlining', 'name' => 'Subrayado o resaltado']);

        $this->expectException(UniqueConstraintViolationException::class);

        Defect::create(['code' => 'underlining2', 'name' => 'Subrayado o resaltado']);
    }
}