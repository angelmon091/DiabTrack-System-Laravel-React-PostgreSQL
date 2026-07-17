<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Clase AppLayout
 *
 * Componente de diseño principal de la aplicación.
 * Proporciona la estructura base para todas las páginas de la aplicación.
 */
class AppLayout extends Component
{
    /**
     * Obtiene la vista que representa el componente.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
