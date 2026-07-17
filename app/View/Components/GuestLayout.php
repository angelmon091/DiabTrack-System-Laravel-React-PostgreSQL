<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Clase GuestLayout
 *
 * Componente de diseño para páginas de invitados.
 * Proporciona la estructura base para páginas públicas de la aplicación.
 */
class GuestLayout extends Component
{
    /**
     * Obtiene la vista que representa el componente.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
