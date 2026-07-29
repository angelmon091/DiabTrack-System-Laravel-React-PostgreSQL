<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminRoleIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    public function test_admin_role_index_is_rendered_with_resource_data(): void
    {
        $role = Role::firstOrCreate(['name' => 'qa-role'], ['description' => 'Rol para QA']);
        $this->withoutVite();
        $this->actingAs($this->admin())->get('/admin/roles')->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Roles/Index')->url('/admin/roles')->where('createUrl', '/admin/roles/create')
            ->has('roles.data')
            ->where('roles.data', fn ($roles) => collect($roles)->contains(fn ($item) => $item['id'] === $role->id
                && $item['name'] === 'qa-role'
                && $item['usersCount'] === 0))
            ->where('adminNavigation.rolesUrl', '/admin/roles'));
    }

    public function test_unused_role_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'eliminable']);
        $this->actingAs($this->admin())->delete(route('admin.roles.destroy', $role))->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_role_with_users_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'en-uso']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $this->actingAs($this->admin())->from('/admin/roles')->delete(route('admin.roles.destroy', $role))->assertRedirect('/admin/roles')->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }
}
