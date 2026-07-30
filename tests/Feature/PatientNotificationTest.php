<?php

namespace Tests\Feature;

use App\Models\PatientNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_read_and_delete_an_owned_notification(): void
    {
        $user = User::factory()->create();
        $notification = PatientNotification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Aviso', 'body' => 'Detalle']);

        $this->actingAs($user)->postJson(route('notifications.read', $notification))->assertOk()->assertJson(['ok' => true]);
        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($user)->deleteJson(route('notifications.destroy', $notification))->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseMissing('patient_notifications', ['id' => $notification->id]);
    }

    public function test_user_cannot_mutate_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $notification = PatientNotification::create(['user_id' => $owner->id, 'type' => 'system', 'title' => 'Privado', 'body' => 'Detalle']);

        $this->actingAs($other)->postJson(route('notifications.read', $notification))->assertForbidden();
        $this->actingAs($other)->deleteJson(route('notifications.destroy', $notification))->assertForbidden();
        $this->assertDatabaseHas('patient_notifications', ['id' => $notification->id, 'read_at' => null]);
    }

    public function test_bulk_actions_only_affect_authenticated_users_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        PatientNotification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Uno', 'body' => 'Detalle']);
        PatientNotification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Dos', 'body' => 'Detalle']);
        $foreign = PatientNotification::create(['user_id' => $other->id, 'type' => 'system', 'title' => 'Ajeno', 'body' => 'Detalle']);

        $this->actingAs($user)->postJson(route('notifications.read-all'))->assertOk();
        $this->assertSame(2, PatientNotification::where('user_id', $user->id)->whereNotNull('read_at')->count());
        $this->assertNull($foreign->fresh()->read_at);

        $this->actingAs($user)->deleteJson(route('notifications.destroy-all'))->assertOk();
        $this->assertDatabaseMissing('patient_notifications', ['user_id' => $user->id]);
        $this->assertDatabaseHas('patient_notifications', ['id' => $foreign->id]);
    }

    public function test_inertia_notification_mutation_receives_a_redirect_instead_of_plain_json(): void
    {
        $user = User::factory()->create();
        $notification = PatientNotification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Aviso', 'body' => 'Detalle']);

        $this->actingAs($user)
            ->from(route('dashboard'))
            ->withHeader('X-Inertia', 'true')
            ->post(route('notifications.read', $notification))
            ->assertRedirect(route('dashboard'));
    }
}
