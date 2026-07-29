<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_is_rendered_with_navigation(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
        $this->actingAs($admin)->get('/admin')->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')->url('/admin')
            ->where('adminNavigation.dashboardUrl', '/admin')
            ->where('adminNavigation.usersUrl', '/admin/users')
            ->where('adminNavigation.rolesUrl', '/admin/roles')
            ->where('adminNavigation.doctorsUrl', '/admin/doctors')
            ->where('adminNavigation.apiUsageUrl', '/admin/api-usage')
            ->where('auth.permissions.esAdmin', true));
    }

    public function test_non_admin_cannot_open_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['email_verified_at' => now()]))
            ->get('/admin')
            ->assertRedirect(route('dashboard'));
    }
}
