<?php

namespace Tests\Feature;

use App\Models\DoctorProfile;
use App\Models\Role;
use App\Models\User;
use App\Notifications\DoctorApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DoctorApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $doctor;
    private DoctorProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->doctor = User::factory()->create();
        $doctorRole = Role::firstOrCreate(['name' => 'médico']);
        $this->doctor->roles()->attach($doctorRole);
        $this->profile = DoctorProfile::create([
            'user_id' => $this->doctor->id,
            'gender' => 'Masculino',
            'license_number' => '12345678',
            'specialty' => 'Medicina General',
            'approval_status' => DoctorProfile::STATUS_PENDING,
        ]);
    }

    public function test_pending_doctor_can_view_dashboard_but_cannot_link_patients(): void
    {
        $this->withoutVite();
        $this->actingAs($this->doctor)->get(route('doctor.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->component('Doctor/Dashboard')->where('approval.approved', false)->where('approval.rejected', false)
            ->where('approval.licenseNumber', '12345678'));
        $this->actingAs($this->doctor)->get(route('doctor.link'))->assertRedirect(route('doctor.dashboard'))->assertSessionHas('warning');
    }

    public function test_admin_can_view_pending_doctor_requests(): void
    {
        $this->withoutVite();
        $this->actingAs($this->admin)->get(route('admin.doctors.index'))->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Doctors/Index')->url('/admin/doctors')
            ->where('filters.status', DoctorProfile::STATUS_PENDING)->where('pendingCount', 1)
            ->where('doctors.data.0.name', $this->doctor->name)->where('doctors.data.0.licenseNumber', '12345678'));
    }

    public function test_admin_approval_enables_linking_and_notifies_doctor(): void
    {
        Notification::fake();
        $this->actingAs($this->admin)->patch(route('admin.doctors.approve', $this->profile), ['review_notes' => 'Cédula verificada en el registro profesional.'])->assertSessionHas('success');
        $this->profile->refresh();
        $this->assertTrue($this->profile->isApproved());
        $this->assertSame($this->admin->id, $this->profile->approved_by);
        $this->assertNotNull($this->profile->approved_at);
        Notification::assertSentTo($this->doctor, DoctorApprovedNotification::class);
        $this->actingAs($this->doctor)->get(route('doctor.link'))->assertOk();
    }

    public function test_rejected_doctor_remains_blocked(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.doctors.reject', $this->profile), ['review_notes' => 'La cédula no pudo validarse.'])->assertSessionHas('success');
        $this->assertSame(DoctorProfile::STATUS_REJECTED, $this->profile->fresh()->approval_status);
        $this->actingAs($this->doctor)->get(route('doctor.link'))->assertRedirect(route('doctor.dashboard'));
    }

    public function test_approved_filter_returns_approved_profiles(): void
    {
        $this->profile->update(['approval_status' => DoctorProfile::STATUS_APPROVED, 'approved_by' => $this->admin->id, 'approved_at' => now()]);
        $this->withoutVite();
        $this->actingAs($this->admin)->get(route('admin.doctors.index', ['status' => DoctorProfile::STATUS_APPROVED]))->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Doctors/Index')->where('filters.status', DoctorProfile::STATUS_APPROVED)
            ->where('doctors.data.0.status', DoctorProfile::STATUS_APPROVED));
    }

    public function test_rejecting_approved_doctor_blocks_next_request_in_same_session_without_notification(): void
    {
        Notification::fake();
        $this->profile->update(['approval_status' => DoctorProfile::STATUS_APPROVED, 'approved_by' => $this->admin->id, 'approved_at' => now()]);
        $this->actingAs($this->doctor)->get(route('doctor.link'))->assertOk();
        $this->actingAs($this->admin)->patch(route('admin.doctors.reject', $this->profile), ['review_notes' => 'La aprobación fue revocada durante el QA.'])->assertSessionHas('success');
        $this->doctor->unsetRelation('doctorProfile');
        $this->actingAs($this->doctor)->get(route('doctor.link'))->assertRedirect(route('doctor.dashboard'))->assertSessionHas('warning');
        $this->profile->refresh();
        $this->assertSame(DoctorProfile::STATUS_REJECTED, $this->profile->approval_status);
        $this->assertNull($this->profile->approved_by);
        $this->assertNull($this->profile->approved_at);
        Notification::assertNothingSent();
    }
}
