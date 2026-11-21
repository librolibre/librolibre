<?php

namespace Tests\Feature\Models;

use App\Models\AdminAction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminActionTest extends TestCase
{
    use RefreshDatabase;

    public static function requiredColumns(): array
    {
        return [
            'action' => ['action'],
            'target type' => ['target_type'],
            'target id' => ['target_id'],
        ];
    }

    /** An action records who, what, on what and why. */
    public function test_factory_creates_an_admin_action(): void
    {
        $action = AdminAction::factory()->create()->fresh();

        $this->assertSame('admin', $action->admin->profile->code);
        $this->assertSame('suspend_user', $action->action);
        $this->assertSame('user', $action->target_type);
        $this->assertNotNull($action->reason);
    }

    /** Automatic suspensions (3 strikes) have no admin (RN-26). */
    public function test_automatic_action_has_no_admin(): void
    {
        $action = AdminAction::factory()->automatic()->create()->fresh();

        $this->assertNull($action->admin_id);
        $this->assertNull($action->admin);
    }

    #[DataProvider('requiredColumns')]
    public function test_required_columns_cannot_be_null(string $column): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('insert into "admin_actions"');

        AdminAction::factory()->create([$column => null]);
    }

    /** admin_id can only be set by our own code. */
    public function test_admin_cannot_be_mass_assigned(): void
    {
        $action = AdminAction::factory()->create();
        $adminId = $action->admin_id;

        $action->fill(['admin_id' => User::factory()->create()->id])->save();

        $this->assertSame($adminId, $action->refresh()->admin_id);
    }

    /** The log must stay on record: an admin with actions cannot be deleted. */
    public function test_admin_with_actions_cannot_be_deleted(): void
    {
        $action = AdminAction::factory()->create();

        $this->expectException(QueryException::class);

        $action->admin->delete();
    }
}
