<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminRoleEditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    public function test_edit_role_screen_is_rendered_with_current_data(): void
    {
        $role = Role::create(['name' => 'editor-qa', 'description' => 'Descripción inicial']);
        $this->withoutVite();
        $this->actingAs($this->admin())->get(route('admin.roles.edit', $role))->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Roles/Edit')->url('/admin/roles/'.$role->id.'/edit')
            ->where('role.data.id', $role->id)->where('role.data.name', 'editor-qa')->where('role.data.description', 'Descripción inicial')->where('role.data.usersCount', 0)
            ->where('updateUrl', '/admin/roles/'.$role->id)->where('indexUrl', '/admin/roles'));
    }

    public function test_role_can_be_saved_without_changing_its_name(): void
    {
        $role = Role::create(['name' => 'mismo-nombre', 'description' => 'Antes']);
        $this->actingAs($this->admin())->put(route('admin.roles.update', $role), ['name' => 'mismo-nombre', 'description' => 'Después'])->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'mismo-nombre', 'description' => 'Después']);
    }

    public function test_role_cannot_use_another_roles_name(): void
    {
        Role::create(['name' => 'ocupado']);
        $role = Role::create(['name' => 'editable']);
        $this->actingAs($this->admin())->from(route('admin.roles.edit', $role))->put(route('admin.roles.update', $role), ['name' => 'ocupado'])->assertSessionHasErrors('name');
        $this->assertSame('editable', $role->fresh()->name);
    }

    public function test_assigned_role_can_still_be_updated_but_not_deleted(): void
    {
        $role = Role::create(['name' => 'asignado']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.roles.update', $role), ['name' => 'asignado-editado', 'description' => 'Permitido'])->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'asignado-editado']);
        $this->actingAs($admin)->from('/admin/roles')->delete(route('admin.roles.destroy', $role))->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }
}
