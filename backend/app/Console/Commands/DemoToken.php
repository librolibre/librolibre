<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

/**
 * php artisan demo:token <email> — a Sanctum token for a demo user (#14).
 * Demo users cannot log in with Google (their google_id is fake), so this is how to act as one of them
 * (an admin, a suspended user…) from the fake front end, Swagger or Angular.
 * Only demo users: the token is valid against the shared API, so it must never work for a real account.
 */
class DemoToken extends Command
{
    protected $signature = 'demo:token {email : Email de un usuario de prueba, p. ej. ane@demo.librolibre.test}';

    protected $description = 'Crea un token de acceso para un usuario de prueba (#14)';

    public function handle(): int
    {
        $email = $this->argument('email');

        // Both marks: the demo email domain and the demo google_id
        $user = User::where('email', $email)
            ->where('email', 'like', '%@'.DemoSeeder::EMAIL_DOMAIN)
            ->where('google_id', 'like', DemoSeeder::GOOGLE_ID_PREFIX.'%')
            ->first();

        if ($user === null) {
            $this->error("«{$email}» no es un usuario de prueba. Solo funciona con correos @".DemoSeeder::EMAIL_DOMAIN.' (carga los datos con: php artisan db:seed --class=DemoSeeder).');

            return self::FAILURE;
        }

        $token = $user->createToken('demo')->plainTextToken;

        $this->info("Token para {$user->username} ({$user->email}, rol {$user->profile->code}, estado {$user->status}):");
        // The token goes last, on its own line, so it is easy to copy
        $this->line($token);

        return self::SUCCESS;
    }
}
