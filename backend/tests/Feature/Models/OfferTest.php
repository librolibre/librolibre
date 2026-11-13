<?php

namespace Tests\Feature\Models;

use App\Models\Book;
use App\Models\Offer;
use App\Models\OfferStatus;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OfferTest extends TestCase
{
    use RefreshDatabase;

    /** Columns that must have a value (NOT NULL in the migration). */
    public static function requiredColumns(): array
    {
        return [
            'type' => ['type'],
            'requester' => ['requester_id'],
            'requested book' => ['book_requested_id'],
            'status' => ['offer_status_id'],
        ];
    }

    /** A new trade is pending, with no counteroffer and nothing confirmed or read. */
    public function test_factory_creates_a_pending_trade(): void
    {
        $offer = Offer::factory()->create()->fresh();

        $this->assertSame('trade', $offer->type);
        $this->assertSame('pending', $offer->status->code);
        $this->assertFalse($offer->counteroffer_made);
        $this->assertNull($offer->owner_confirmed_at);
        $this->assertNull($offer->requester_confirmed_at);
        $this->assertNull($offer->owner_last_read_at);
        $this->assertNull($offer->requester_last_read_at);
    }

    /** In a trade, the offered book belongs to the requester. */
    public function test_offered_book_belongs_to_the_requester(): void
    {
        $offer = Offer::factory()->create();

        $this->assertSame($offer->requester_id, $offer->offeredBook->user_id);
    }

    /** A donation has no offered book (book_offered_id is nullable). */
    public function test_donation_has_no_offered_book(): void
    {
        $offer = Offer::factory()->donation()->create()->fresh();

        $this->assertSame('donation', $offer->type);
        $this->assertNull($offer->book_offered_id);
        $this->assertNull($offer->offeredBook);
    }

    // donation() so the offered-book closure doesn't fail first
    #[DataProvider('requiredColumns')]
    public function test_required_columns_cannot_be_null(string $column): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('insert into "offers"');

        Offer::factory()->donation()->create([$column => null]);
    }

    /** The requester, the status and the counteroffer flag can only be changed by our own code. */
    public function test_protected_fields_cannot_be_mass_assigned(): void
    {
        $offer = Offer::factory()->create();
        $requesterId = $offer->requester_id;
        $statusId = $offer->offer_status_id;

        $offer->fill([
            'requester_id' => User::factory()->create()->id,
            'offer_status_id' => OfferStatus::create(['code' => 'accepted', 'name' => 'Aceptada'])->id,
            'counteroffer_made' => true,
        ])->save();

        $offer->refresh();
        $this->assertSame($requesterId, $offer->requester_id);
        $this->assertSame($statusId, $offer->offer_status_id);
        $this->assertFalse($offer->counteroffer_made);
    }

    /** A book that is part of an offer cannot be deleted (no cascade on the book foreign keys). */
    public function test_book_with_offers_cannot_be_deleted(): void
    {
        $offer = Offer::factory()->create();

        $this->expectException(QueryException::class);

        $offer->requestedBook->delete();
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function test_offer_belongs_to_its_requester(): void
    {
        $user = User::factory()->create();
        $offer = Offer::factory()->create(['requester_id' => $user->id]);

        $this->assertTrue($offer->requester->is($user));
        $this->assertTrue($user->offers->contains($offer));
    }

    public function test_offer_belongs_to_the_requested_book(): void
    {
        $book = Book::factory()->create();
        $offer = Offer::factory()->create(['book_requested_id' => $book->id]);

        $this->assertTrue($offer->requestedBook->is($book));
    }
}
