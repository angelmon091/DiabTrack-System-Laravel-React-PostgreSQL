<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUserEditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    public function test_edit_screen_is_preloaded_with_database_roles(): void
    {
        $admin = $this->admin();
        $role = Role::create(['name' => 'editable-dinamico']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $this->withoutVite();
        $this->actingAs($admin)->get(route('admin.users.edit', $user))->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Edit')->url('/admin/users/'.$user->id.'/edit')
            ->where('user.data.email', $user->email)->where('user.data.roles.0.id', $role->id)
            ->where('roles', fn ($roles) => collect($roles)->contains(fn ($item) => $item['name'] === 'editable-dinamico')));
    }

    public function test_user_can_keep_own_email_and_password_while_updating_roles(): void
    {
        $admin = $this->admin();
        $role = Role::create(['name' => 'nuevo-rol']);
        $user = User::factory()->create(['password' => 'password-original']);
        $originalHash = $user->password;
        $this->actingAs($admin)->put(route('admin.users.update', $user), ['name' => 'Nombre actualizado', 'email' => $user->email, 'password' => '', 'password_confirmation' => '', 'roles' => [$role->id]])->assertRedirect('/admin/users')->assertSessionHas('success');
        $user->refresh();
        $this->assertSame('Nombre actualizado', $user->name);
        $this->assertSame($originalHash, $user->password);
        $this->assertTrue($user->roles->contains($role));
    }

    public function test_user_cannot_take_another_users_email(): void
    {
        $admin = $this->admin();
        $existing = User::factory()->create(['email' => 'ocupado@example.test']);
        $user = User::factory()->create(['email' => 'editable@example.test']);
        $this->actingAs($admin)->from(route('admin.users.edit', $user))->put(route('admin.users.update', $user), ['name' => $user->name, 'email' => strtoupper($existing->email), 'password' => '', 'password_confirmation' => ''])->assertRedirect(route('admin.users.edit', $user))->assertSessionHasErrors('email');
        $this->assertSame('editable@example.test', $user->fresh()->email);
    }

    public function test_admin_cannot_revoke_their_own_admin_access(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.users.update', $admin), ['name' => $admin->name, 'email' => $admin->email, 'password' => '', 'password_confirmation' => ''])->assertRedirect('/admin/users');
        $this->assertTrue($admin->fresh()->is_admin);
    }
}
