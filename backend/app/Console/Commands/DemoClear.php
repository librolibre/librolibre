<?php

namespace App\Console\Commands;

use App\Models\AdminAction;
use App\Models\Book;
use App\Models\Offer;
use App\Models\User;
use App\Models\UserReport;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * php artisan demo:clear — removes all the demo data (#14): the users @demo.librolibre.test and everything
 * that points to them. Real users and their data are never touched.
 * Run it before the app goes live (#80). Safe to run again: with no demo data it does nothing.
 */
class DemoClear extends Command
{
    protected $signature = 'demo:clear {--force : No pedir confirmación}';

    protected $description = 'Borra todos los datos de prueba: usuarios @'.DemoSeeder::EMAIL_DOMAIN.' y lo que dependa de ellos (#14)';

    public function handle(): int
    {
        if (! $this->option('force')
            && ! $this->confirm('¿Borrar todos los datos de prueba (usuarios @'.DemoSeeder::EMAIL_DOMAIN.' y sus libros)?')) {
            $this->info('No se ha borrado nada.');

            return self::SUCCESS;
        }

        $userIds = User::where('email', 'like', '%@'.DemoSeeder::EMAIL_DOMAIN)->pluck('id');

        if ($userIds->isEmpty()) {
            $this->info('No hay datos de prueba.');

            return self::SUCCESS;
        }

        $bookIds = Book::whereIn('user_id', $userIds)->pluck('id');

        // All or nothing; the order respects the foreign keys (children first)
        DB::transaction(function () use ($userIds, $bookIds) {
            $this->deleteOffersAndReports($userIds, $bookIds);

            // Admin actions taken by a demo admin, or about a demo user or book
            AdminAction::whereIn('admin_id', $userIds)
                ->orWhere(fn ($query) => $query->where('target_type', 'user')->whereIn('target_id', $userIds))
                ->orWhere(fn ($query) => $query->where('target_type', 'book')->whereIn('target_id', $bookIds))
                ->delete();

            // Tokens and notifications are polymorphic (no foreign key): deleted by hand
            DB::table('personal_access_tokens')->where('tokenable_type', User::class)->whereIn('tokenable_id', $userIds)->delete();
            DB::table('notifications')->where('notifiable_type', User::class)->whereIn('notifiable_id', $userIds)->delete();

            // Books (their categories, defects and photos go with them: cascade)
            Book::whereIn('id', $bookIds)->delete();

            // Users (their languages and blocks go with them: cascade)
            User::whereIn('id', $userIds)->delete();
        });

        $this->info("Borrados {$userIds->count()} usuarios de prueba y {$bookIds->count()} libros.");

        return self::SUCCESS;
    }

    /**
     * Offers and reports do not exist yet (Sprints 3–4), but once they do, a real user may have
     * asked for a demo book: those rows point to demo data and must go first.
     *
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $bookIds
     */
    private function deleteOffersAndReports(Collection $userIds, Collection $bookIds): void
    {
        $offerIds = Offer::whereIn('requester_id', $userIds)
            ->orWhereIn('book_requested_id', $bookIds)
            ->orWhereIn('book_offered_id', $bookIds)
            ->pluck('id');

        UserReport::whereIn('reporter_user_id', $userIds)
            ->orWhereIn('reported_user_id', $userIds)
            ->orWhereIn('offer_id', $offerIds)
            ->delete();
        // A demo admin may have resolved a real report: keep the report, forget who resolved it
        UserReport::whereIn('resolved_by', $userIds)->update(['resolved_by' => null]);

        // Their messages and ratings go with them (cascade)
        Offer::whereIn('id', $offerIds)->delete();

        // A demo user's message left in a real conversation: keep it as a system message
        DB::table('messages')->whereIn('user_sender_id', $userIds)->update(['user_sender_id' => null]);
    }
}
