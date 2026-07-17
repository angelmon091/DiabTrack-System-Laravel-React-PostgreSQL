<?php

namespace Tests\Feature;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PurgeUnverifiedUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_only_deletes_unverified_users_older_than_twenty_four_hours(): void
    {
        $expired = User::factory()->unverified()->create([
            'email' => 'expired@example.com',
            'created_at' => now()->subHours(25),
        ]);
        $recent = User::factory()->unverified()->create([
            'email' => 'recent@example.com',
            'created_at' => now()->subHours(23),
        ]);
        $verified = User::factory()->create([
            'email' => 'verified@example.com',
            'created_at' => now()->subDays(10),
        ]);

        EmailVerificationCode::create([
            'user_id' => $expired->id,
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->subHours(24),
            'sent_at' => now()->subHours(25),
        ]);

        $this->artisan('app:purge-unverified-users')
            ->expectsOutput('Cuentas no verificadas eliminadas: 1.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $expired->id]);
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $expired->id]);
        $this->assertDatabaseHas('users', ['id' => $recent->id]);
        $this->assertDatabaseHas('users', ['id' => $verified->id]);
    }
}
