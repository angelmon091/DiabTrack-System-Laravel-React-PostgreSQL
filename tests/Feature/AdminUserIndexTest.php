<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUserIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    public function test_user_index_is_rendered_with_resource_data(): void
    {
        $admin = $this->admin();
        $role = Role::create(['name' => 'qa-user-role']);
        $user = User::factory()->create(['name' => 'Usuario QA', 'email' => 'usuario.qa@example.test']);
        $user->roles()->attach($role);
        $this->withoutVite();
        $this->actingAs($admin)->get('/admin/users')->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')->url('/admin/users')
            ->where('createUrl', '/admin/users/create')->where('filters.search', null)
            ->where('users.data', fn ($users) => collect($users)->contains(fn ($item) => $item['id'] === $user->id && $item['roles'][0]['name'] === 'qa-user-role')));
    }

    public function test_users_can_be_filtered_by_name_or_email(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Coincidencia Única', 'email' => 'uno@example.test']);
        User::factory()->create(['name' => 'Otro usuario', 'email' => 'otro@example.test']);
        $this->withoutVite();
        $this->actingAs($admin)->get('/admin/users?search=Coincidencia')->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', 'Coincidencia')->has('users.data', 1)->where('users.data.0.name', 'Coincidencia Única'));
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->from('/admin/users')->delete(route('admin.users.destroy', $admin))->assertRedirect('/admin/users')->assertSessionHas('error', 'No puedes eliminarte a ti mismo.');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
