<?php

namespace Tests\Feature\Models;

use App\Models\Profile;
use App\Models\ReportReason;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserReportTest extends TestCase
{
    use RefreshDatabase;

    public static function requiredColumns(): array
    {
        return [
            'reporter' => ['reporter_user_id'],
            'reported user' => ['reported_user_id'],
            'reason' => ['reason_id'],
        ];
    }

    public static function optionalColumns(): array
    {
        return [
            'offer' => ['offer_id'],
            'description' => ['description'],
            'resolver' => ['resolved_by'],
            'resolution note' => ['resolution_note'],
            'resolved at' => ['resolved_at'],
        ];
    }

    public function test_factory_creates_an_open_report(): void
    {
        $report = UserReport::factory()->create()->fresh();

        $this->assertSame('open', $report->status);
        $this->assertFalse($report->strike_added);
        $this->assertNull($report->resolved_by);
        $this->assertNull($report->resolution_note);
        $this->assertNull($report->resolved_at);
    }

    #[DataProvider('requiredColumns')]
    public function test_required_columns_cannot_be_null(string $column): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('insert into "user_reports"');

        UserReport::factory()->create([$column => null]);
    }

    #[DataProvider('optionalColumns')]
    public function test_optional_columns_can_be_null(string $column): void
    {
        $report = UserReport::factory()->create([$column => null])->fresh();

        $this->assertNull($report->$column);
    }

    /** reporter, status, strike and resolution can only be changed by our own code. */
    public function test_protected_fields_cannot_be_mass_assigned(): void
    {
        $report = UserReport::factory()->create();
        $reporterId = $report->reporter_user_id;

        $report->fill([
            'reporter_user_id' => User::factory()->create()->id,
            'status' => 'valid',
            'strike_added' => true,
            'resolved_by' => User::factory()->create()->id,
            'resolution_note' => 'Hecho a mano',
            'resolved_at' => now(),
        ])->save();

        $report->refresh();
        $this->assertSame($reporterId, $report->reporter_user_id);
        $this->assertSame('open', $report->status);
        $this->assertFalse($report->strike_added);
        $this->assertNull($report->resolved_by);
        $this->assertNull($report->resolution_note);
        $this->assertNull($report->resolved_at);
    }

    /** A valid report adds a strike and keeps who, when and why (RN-24). */
    public function test_valid_report_keeps_who_when_and_why(): void
    {
        $report = UserReport::factory()->resolved()->create()->fresh();

        $this->assertSame('valid', $report->status);
        $this->assertTrue($report->strike_added);
        $this->assertSame('admin', $report->resolver->profile->code);
        $this->assertNotNull($report->resolution_note);
        $this->assertNotNull($report->resolved_at);
    }

    /** A dismissed report adds no strike. */
    public function test_dismissed_report_adds_no_strike(): void
    {
        $report = UserReport::factory()->resolved('dismissed')->create()->fresh();

        $this->assertSame('dismissed', $report->status);
        $this->assertFalse($report->strike_added);
    }

    /** Reports must stay on record (RN-24): a reason in use cannot be deleted. */
    public function test_reason_in_use_cannot_be_deleted(): void
    {
        $report = UserReport::factory()->create();

        $this->expectException(QueryException::class);

        $report->reason->delete();
    }

    /** Reports must stay on record (RN-24): a reported user cannot be deleted. */
    public function test_reported_user_cannot_be_deleted(): void
    {
        $report = UserReport::factory()->create();

        $this->expectException(QueryException::class);

        $report->reported->delete();
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function test_report_belongs_to_reporter_and_reported_user(): void
    {
        $reporter = User::factory()->create();
        $reported = User::factory()->create();
        $report = UserReport::factory()->create([
            'reporter_user_id' => $reporter->id,
            'reported_user_id' => $reported->id,
        ]);

        $this->assertTrue($report->reporter->is($reporter));
        $this->assertTrue($report->reported->is($reported));
    }

    public function test_report_belongs_to_a_reason(): void
    {
        $reason = ReportReason::create(['code' => 'harassment', 'name' => 'Acoso o insultos']);
        $report = UserReport::factory()->create(['reason_id' => $reason->id]);

        $this->assertTrue($report->reason->is($reason));
    }

    /** Reported from an offer: offer_id points to an exchange between the same two users. */
    public function test_report_belongs_to_an_offer(): void
    {
        $report = UserReport::factory()->fromOffer()->create();

        $this->assertSame($report->offer->requester_id, $report->reporter_user_id);
        $this->assertSame($report->offer->requestedBook->user_id, $report->reported_user_id);
    }

    /** resolved_by points to the admin who resolved it. */
    public function test_report_belongs_to_its_resolver(): void
    {
        $admin = User::factory()->create([
            'profile_id' => Profile::firstOrCreate(['code' => 'admin'], ['name' => 'Administrador'])->id,
        ]);
        $report = UserReport::factory()->resolved()->create(['resolved_by' => $admin->id]);

        $this->assertTrue($admin->is($report->resolver));
    }
}