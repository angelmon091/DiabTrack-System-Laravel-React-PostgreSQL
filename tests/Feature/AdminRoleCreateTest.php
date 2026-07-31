<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminRoleCreateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    public function test_create_role_screen_is_rendered(): void
    {
        $this->withoutVite();
        $this->actingAs($this->admin())->get('/admin/roles/create')->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Roles/Create')->url('/admin/roles/create')->where('storeUrl', '/admin/roles')->where('indexUrl', '/admin/roles')->where('adminNavigation.rolesUrl', '/admin/roles'));
    }

    public function test_admin_can_create_role(): void
    {
        $this->actingAs($this->admin())->post('/admin/roles', ['name' => 'nutricionista', 'description' => 'Acceso nutricional'])->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'nutricionista', 'description' => 'Acceso nutricional']);
    }

    public function test_role_name_must_be_unique(): void
    {
        Role::firstOrCreate(['name' => 'paciente']);
        $this->actingAs($this->admin())->from('/admin/roles/create')->post('/admin/roles', ['name' => 'paciente'])->assertRedirect('/admin/roles/create')->assertSessionHasErrors('name');
    }
}
