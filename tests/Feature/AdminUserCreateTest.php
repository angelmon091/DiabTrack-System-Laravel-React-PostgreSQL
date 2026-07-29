<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUserCreateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    public function test_create_screen_uses_roles_from_database(): void
    {
        $role = Role::create(['name' => 'rol-dinamico', 'description' => 'Desde base de datos']);
        $this->withoutVite();
        $this->actingAs($this->admin())->get('/admin/users/create')->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Create')->url('/admin/users/create')->where('storeUrl', '/admin/users')
            ->where('roles', fn ($roles) => collect($roles)->contains(fn ($item) => $item['id'] === $role->id && $item['name'] === 'rol-dinamico')));
    }

    public function test_admin_defines_initial_password_and_role_without_sending_mail(): void
    {
        Notification::fake();
        $role = Role::create(['name' => 'asignable']);
        $this->actingAs($this->admin())->post('/admin/users', ['name' => 'Usuario creado', 'email' => 'CREADO@EXAMPLE.TEST', 'password' => 'password-seguro', 'password_confirmation' => 'password-seguro', 'roles' => [$role->id], 'is_admin' => true])->assertRedirect('/admin/users')->assertSessionHas('success');
        $user = User::where('email', 'creado@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('password-seguro', $user->password));
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->roles->contains($role));
        Notification::assertNothingSent();
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'duplicado@example.test']);
        $this->actingAs($this->admin())->from('/admin/users/create')->post('/admin/users', ['name' => 'Duplicado', 'email' => 'DUPLICADO@example.test', 'password' => 'password-seguro', 'password_confirmation' => 'password-seguro'])->assertRedirect('/admin/users/create')->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'duplicado@example.test')->count());
    }
}
