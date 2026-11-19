<?php

namespace Feature\Api\ReferenceData;

use App\Models\Language;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_languages_returns_the_nine_seeded_languages(): void
    {
        $this->seed(LanguageSeeder::class);
        $expected = ['es', 'eu', 'en', 'fr', 'ca', 'gl', 'de', 'pt', 'it'];
        $this->assertEqualsCanonicalizing($expected, Language::pluck('code')->all());
    }

    public function test_languages_only_show_code_and_name(): void
    {}

    public function test_languages_do_not_require_a_token(): void
    {}
}