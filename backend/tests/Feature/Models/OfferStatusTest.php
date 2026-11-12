<?php

namespace Tests\Feature\Models;

use App\Models\OfferStatus;
use Database\Seeders\OfferStatusSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferStatusTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder creates every offer status (same codes as the API contract). */
    public function test_seeder_creates_all_expected_offer_statuses(): void
    {
        $this->seed(OfferStatusSeeder::class);
        $expected = [
            'pending', 'counteroffer', 'accepted', 'completed', 'rejected', 'cancelled',
            ];
        $this->assertEqualsCanonicalizing($expected, OfferStatus::pluck('code')->all());
    }

    /** Running the seeder again (for example on Neon) must not duplicate rows. */
    public function test_offer_status_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(OfferStatusSeeder::class);
        $countFirstRun = OfferStatus::count();

        $this->seed(OfferStatusSeeder::class);

        $this->assertSame($countFirstRun, OfferStatus::count());
    }

    /** `code` is what the code compares against, so it can never repeat. */
    public function test_offer_status_code_must_be_unique(): void
    {
        OfferStatus::create(['code' => 'pending', 'name' => 'Pendiente']);

        $this->expectException(UniqueConstraintViolationException::class);

        OfferStatus::create(['code' => 'pending', 'name' => 'En espera']);
    }

    /** Two statuses with the same name would look identical in the request list. */
    public function test_offer_status_name_must_be_unique(): void
    {
        OfferStatus::create(['code' => 'pending', 'name' => 'Pendiente']);

        $this->expectException(UniqueConstraintViolationException::class);

        OfferStatus::create(['code' => 'pending2', 'name' => 'Pendiente']);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    // public function test_offer_status_has_many_offers(): void
    // {
    //     $status = OfferStatus::create(['code' => 'pending', 'name' => 'Pendiente']);
    //     $offer = Offer::factory()->create(['offer_status_id' => $status->id]);

    //     $this->assertTrue($status->offers->contains($offer));
    // }
}
