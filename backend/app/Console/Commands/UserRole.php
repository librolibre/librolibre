<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * php artisan user:role <email> <role> — gives an account a role (#32, RN-19), e.g. makes it an admin.
 * The API can never change a role (profile_id is not fillable and PATCH /me ignores it), so this runs on the
 * server by hand. The person must have logged in with Google at least once, so their account exists.
 */
class UserRole extends Command
{
    protected $signature = 'user:role
        {email : Email de la cuenta (con el que entra con Google)}
        {role : Código del rol: admin o user}';

    protected $description = 'Cambia el rol de una cuenta (p. ej. la hace administradora) (#32)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $role = Profile::where('code', $this->argument('role'))->first();

        if ($role === null) {
            $this->error("El rol «{$this->argument('role')}» no existe. Roles: ".Profile::pluck('code')->join(', ').'.');

            return self::FAILURE;
        }

        // Deleted accounts are anonymised (#29): they cannot get a role back
        $user = User::whereRaw('lower(email) = ?', [mb_strtolower($email)])
            ->where('status', '!=', 'deleted')
            ->first();

        if ($user === null) {
            $this->error("No hay ninguna cuenta con el email «{$email}». Tiene que haber entrado con Google al menos una vez.");

            return self::FAILURE;
        }

        if ($user->profile_id === $role->id) {
            $this->info("{$user->email} ya tiene el rol {$role->code}. No se ha cambiado nada.");

            return self::SUCCESS;
        }

        // Without any admin left, nobody could reach the admin panel
        if ($user->isAdmin() && $this->adminCount() <= 1) {
            $this->error("{$user->email} es el último administrador: no se le puede quitar el rol.");

            return self::FAILURE;
        }

        $previous = $user->profile?->code;

        // Not fillable on purpose, so it is assigned directly
        $user->profile_id = $role->id;
        $user->save();

        $this->info("{$user->email}: rol {$previous} → {$role->code}.");

        return self::SUCCESS;
    }

    private function adminCount(): int
    {
        return User::whereHas('profile', fn ($query) => $query->where('code', 'admin'))
            ->where('status', '!=', 'deleted')
            ->count();
    }
}
