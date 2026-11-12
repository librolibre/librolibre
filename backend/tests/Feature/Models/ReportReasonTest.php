<?php

namespace Tests\Feature\Models;

use App\Models\ReportReason;
use Database\Seeders\ReportReasonSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportReasonTest extends TestCase
{
    use RefreshDatabase;

    /** The seeder creates every report reason (same codes as the API contract). */
    public function test_seeder_creates_all_expected_report_reasons(): void
    {
        $this->seed(ReportReasonSeeder::class);
        $expected = [
            'harassment', 'offensive_content', 'spam', 'sale_attempt',
            'false_information', 'impersonation', 'no_show', 'other',
            ];
        $this->assertEqualsCanonicalizing($expected, ReportReason::pluck('code')->all());
    }

    /** Only "other" forces the user to write a description. */
    public function test_only_other_requires_a_description(): void
    {
        $this->seed(ReportReasonSeeder::class);

        $this->assertSame(
            ['other'],
            ReportReason::where('requires_description', true)->pluck('code')->all(),
        );
    }

    /** Running the seeder again (for example on Neon) must not duplicate rows. */
    public function test_report_reason_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(ReportReasonSeeder::class);
        $countFirstRun = ReportReason::count();

        $this->seed(ReportReasonSeeder::class);

        $this->assertSame($countFirstRun, ReportReason::count());
    }

    /** A new reason does not require a description unless it says so. */
    public function test_requires_description_defaults_to_false(): void
    {
        $reason = ReportReason::create(['code' => 'spam', 'name' => 'Spam'])->fresh();

        $this->assertFalse($reason->requires_description);
    }

    /** `code` is what the code compares against, so it can never repeat. */
    public function test_report_reason_code_must_be_unique(): void
    {
        ReportReason::create(['code' => 'spam', 'name' => 'Spam']);

        $this->expectException(UniqueConstraintViolationException::class);

        ReportReason::create(['code' => 'spam', 'name' => 'Publicidad']);
    }

    /** Two reasons with the same name would look identical in the report form. */
    public function test_report_reason_name_must_be_unique(): void
    {
        ReportReason::create(['code' => 'spam', 'name' => 'Spam']);

        $this->expectException(UniqueConstraintViolationException::class);

        ReportReason::create(['code' => 'spam2', 'name' => 'Spam']);
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    // public function test_report_reason_has_many_user_reports(): void
    // {
    //     $reason = ReportReason::create(['code' => 'spam', 'name' => 'Spam']);
    //     $report = UserReport::factory()->create(['reason_id' => $reason->id]);

    //     $this->assertTrue($reason->userReports->contains($report));
    // }
}
