<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:purge-unverified-users')]
#[Description('Elimina cuentas que no verificaron su correo después de 24 horas')]
/**
 * Elimina de forma controlada las cuentas que no verificaron su correo dentro del plazo establecido.
 */
class PurgeUnverifiedUsers extends Command
{
    public function handle(): int
    {
        $deleted = 0;
        $expiredBefore = now()->subHours(24);

        User::query()
            ->whereNull('email_verified_at')
            ->where('created_at', '<=', $expiredBefore)
            ->select(['id', 'email'])
            ->chunkById(100, function ($users) use (&$deleted): void {
                DB::transaction(function () use ($users, &$deleted): void {
                    DB::table('password_reset_tokens')
                        ->whereIn('email', $users->pluck('email'))
                        ->delete();

                    $deleted += User::whereKey($users->pluck('id'))->delete();
                });
            });

        $this->info("Cuentas no verificadas eliminadas: {$deleted}.");

        return self::SUCCESS;
    }
}
