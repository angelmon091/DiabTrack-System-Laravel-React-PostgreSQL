<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CriticalPatientFlowTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_guest_can_open_authentication_pages(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertInputPresent('email')
                ->assertInputPresent('password')
                ->visit('/register')
                ->assertInputPresent('name')
                ->assertInputPresent('password_confirmation');
        });
    }

    public function test_new_patient_completes_registration_and_onboarding(): void
    {
        $email = 'e2e.patient@example.test';

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit('/register')
                ->type('name', 'Paciente E2E')
                ->type('email', $email)
                ->type('password', 'Password123!')
                ->type('password_confirmation', 'Password123!')
                ->press('Registrarse')
                ->assertPathIs('/verify-email');

            // La validación del código se cubre en la suite Feature. En este recorrido
            // se habilita la cuenta para continuar con el onboarding en el navegador.
            User::query()->where('email', $email)->update(['email_verified_at' => now()]);

            $browser->visit('/onboarding')
                ->assertPathIs('/onboarding')
                ->clickLink('Soy Paciente')
                ->assertPathIs('/onboarding/patient')
                ->select('birth_day', '10')
                ->select('birth_month', 'Julio')
                ->select('birth_year', '1990')
                ->select('diabetes_type', 'Diabetes Mellitus Tipo 2')
                ->type('weight', '75')
                ->type('height', '170')
                ->radio('gender', 'Masculino')
                ->press('Registrar Datos')
                ->assertPathIs('/dashboard')
                ->visit('/tracking/vitals')
                ->assertInputPresent('glucose_level');

            $browser->script("const input = document.querySelector('input[name=glucose_level]'); input.value = 145; document.querySelector('form.tracking-form-layout').submit();");

            $browser->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard')
                ->visit('/tracking/summary')
                ->assertPathIs('/tracking/summary')
                ->assertSee('Dinámica de Glucosa')
                ->assertSee('Glucosa por Momento del Día')
                ->assertPresent('#mainDetailedChart')
                ->assertPresent('#glucoseMomentChart')
                ->assertScript('typeof Chart !== "undefined" && Object.keys(Chart.instances).length >= 2', true);
        });

        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertDatabaseHas('patient_profiles', ['weight' => 75]);
        $this->assertDatabaseHas('vital_signs', [
            'glucose_level' => 145,
            'measurement_moment' => 'Ayunas',
        ]);
    }
}
