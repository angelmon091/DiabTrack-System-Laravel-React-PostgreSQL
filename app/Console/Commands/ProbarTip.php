<?php

namespace App\Console\Commands;

use App\Services\TipService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tips:probar')]
#[Description('Prueba la generación de tips con Anthropic y reglas clínicas de respaldo')]
/**
 * Permite comprobar desde consola la generación de consejos y su mecanismo de respaldo.
 */
class ProbarTip extends Command
{
    public function handle(TipService $tipService): int
    {
        $resultado = $tipService->generarTip([
            'tipo_diabetes' => 2,
            'edad' => 45,
            'glucosa' => 190,
            'hba1c' => 8.2,
            'imc' => 28.5,
        ]);

        $this->info('Fuente: '.$resultado['fuente']);
        $this->line('Label: '.$resultado['label']);
        $this->line('Tip: '.$resultado['tip']);

        return self::SUCCESS;
    }
}
