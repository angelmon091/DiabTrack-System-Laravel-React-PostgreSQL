<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Comprueba que la página principal responda correctamente.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->url('/')
                ->where('loginUrl', '/login')
                ->where('registerUrl', '/register')
                ->where('dashboardUrl', '/dashboard')
                ->has('homeUrl')
                ->has('ogImageUrl')
                ->has('year'));
    }
}
