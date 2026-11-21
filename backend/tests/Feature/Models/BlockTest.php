<?php

namespace Tests\Feature\Models;

use App\Models\Block;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockTest extends TestCase
{
    use RefreshDatabase;

    /** The same user can only be blocked once by the same person. */
    public function test_user_cannot_block_the_same_user_twice(): void
    {
        $block = Block::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        Block::factory()->create([
            'user_blocker_id' => $block->user_blocker_id,
            'user_blocked_id' => $block->user_blocked_id,
        ]);
    }

    /** Blocking works in both directions independently: B can also block A. */
    public function test_both_users_can_block_each_other(): void
    {
        $block = Block::factory()->create();

        Block::factory()->create([
            'user_blocker_id' => $block->user_blocked_id,
            'user_blocked_id' => $block->user_blocker_id,
        ]);

        $this->assertSame(2, Block::count());
    }

    /** The CHECK constraint stops a user blocking themselves. */
    public function test_user_cannot_block_themselves(): void
    {
        $user = User::factory()->create();

        $this->expectException(QueryException::class);

        Block::factory()->create(['user_blocker_id' => $user->id, 'user_blocked_id' => $user->id]);
    }

    /** Deleting a user also deletes their blocks, in both directions (cascade). */
    public function test_deleting_user_removes_their_blocks(): void
    {
        $block = Block::factory()->create();

        $block->blocker->delete();

        $this->assertDatabaseMissing('blocks', ['id' => $block->id]);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function test_block_belongs_to_blocker_and_blocked(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();
        $block = Block::factory()->create(['user_blocker_id' => $blocker->id, 'user_blocked_id' => $blocked->id]);

        $this->assertTrue($block->blocker->is($blocker));
        $this->assertTrue($block->blocked->is($blocked));
    }

    /** User::blockedUsers() reads the blocks table. */
    public function test_user_has_many_blocked_users(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();

        $blocker->blockedUsers()->attach($blocked);

        $this->assertTrue($blocker->blockedUsers->contains($blocked));
        $this->assertFalse($blocked->blockedUsers->contains($blocker));
    }
}
