<?php

namespace Tests\Feature\Models;

use App\Models\Message;
use App\Models\Offer;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    /** A normal message is text written by one of the two parties. */
    public function test_factory_creates_a_text_message(): void
    {
        $message = Message::factory()->create()->fresh();

        $this->assertSame('text', $message->type);
        $this->assertNotNull($message->body);
        $this->assertSame($message->offer->requester_id, $message->user_sender_id);
    }

    /** System cards (Wallapop style) have no sender and no text, only a type and a payload. */
    public function test_system_card_has_no_sender_and_keeps_its_payload(): void
    {
        $message = Message::factory()->card('counteroffer', ['book_id' => 12])->create()->fresh();

        $this->assertNull($message->user_sender_id);
        $this->assertNull($message->sender);
        $this->assertNull($message->body);
        $this->assertSame(['book_id' => 12], $message->payload);
    }

    /** A message always belongs to an offer. */
    public function test_offer_is_required(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('insert into "messages"');

        Message::factory()->create(['offer_id' => null, 'user_sender_id' => null]);
    }

    /** Deleting an offer also deletes its chat (cascade on offer_id). */
    public function test_deleting_offer_removes_its_messages(): void
    {
        $message = Message::factory()->create();

        $message->offer->delete();

        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function test_message_belongs_to_an_offer(): void
    {
        $offer = Offer::factory()->create();
        $message = Message::factory()->create(['offer_id' => $offer->id]);

        $this->assertTrue($message->offer->is($offer));
        $this->assertTrue($offer->messages->contains($message));
    }

    public function test_message_belongs_to_its_sender(): void
    {
        $message = Message::factory()->create();

        $this->assertTrue($message->sender->is($message->offer->requester));
    }
}
