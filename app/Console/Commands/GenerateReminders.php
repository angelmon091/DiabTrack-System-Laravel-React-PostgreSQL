<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PatientReminderService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:generate-reminders')]
#[Description('Genera recordatorios IA breves para pacientes que aún no han registrado sus datos de hoy')]
/**
 * Ejecuta la generación programada de recordatorios para pacientes sin registros recientes.
 */
class GenerateReminders extends Command
{
    public function __construct(private readonly PatientReminderService $reminderService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $patients = User::whereHas('roles', function ($query) {
            $query->where('name', 'paciente');
        })->get();

        if ($patients->isEmpty()) {
            $this->info('No se encontraron pacientes para generar recordatorios.');

            return;
        }

        // Solo pacientes con actividad reciente: no molestamos cuentas abandonadas.
        $maxInactivityDays = (int) env('DAILY_TIPS_MAX_INACTIVITY_DAYS', 3);
        $since = Carbon::now()->subDays($maxInactivityDays);

        $creados = 0;

        foreach ($patients as $patient) {
            $hasRecentData = $patient->vitalSigns()->where('created_at', '>=', $since)->exists() ||
                             $patient->activityLogs()->where('created_at', '>=', $since)->exists() ||
                             $patient->nutritionLogs()->where('created_at', '>=', $since)->exists() ||
                             DB::table('symptom_user')->where('user_id', $patient->id)->where('logged_at', '>=', $since)->exists();

            if (! $hasRecentData) {
                continue;
            }

            $notification = $this->reminderService->generateFor($patient);

            if ($notification) {
                $creados++;
                $this->line("  → Recordatorio IA para {$patient->name} (ID {$patient->id}): {$notification->title}");
            }
        }

        $this->info("Recordatorios generados: {$creados}.");
    }
}
