<?php

namespace App\Providers;

use App\Models\User;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The admin routes use the "can:admin" middleware, which asks this Gate (#32).
        // Who counts as an admin is decided in User::isAdmin() (profile code "admin", RN-19).
        // No token: auth:sanctum answers 401 before this runs. Not an admin: can:admin answers 403.
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer', 'Sanctum')
                );
            });
    }
}
