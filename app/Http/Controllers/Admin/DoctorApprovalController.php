<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Notifications\DoctorApprovedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestiona la revisión administrativa, aprobación y rechazo de los perfiles médicos.
 */
class DoctorApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: DoctorProfile::STATUS_PENDING;
        $allowedStatuses = [DoctorProfile::STATUS_PENDING, DoctorProfile::STATUS_APPROVED, DoctorProfile::STATUS_REJECTED, 'all'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = DoctorProfile::STATUS_PENDING;
        }

        $doctors = DoctorProfile::query()
            ->with(['user', 'approver'])
            ->when($status !== 'all', fn ($query) => $query->where('approval_status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $pendingCount = DoctorProfile::where('approval_status', DoctorProfile::STATUS_PENDING)->count();

        return view('admin.doctors.index', compact('doctors', 'status', 'pendingCount'));
    }

    public function approve(Request $request, DoctorProfile $doctorProfile): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $wasApproved = $doctorProfile->isApproved();

        $doctorProfile->update([
            'approval_status' => DoctorProfile::STATUS_APPROVED,
            'review_notes' => $validated['review_notes'] ?? null,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        if (! $wasApproved) {
            $doctorProfile->user->notify(new DoctorApprovedNotification);
        }

        return back()->with('success', 'El perfil médico fue aprobado y se notificó al profesional.');
    }

    public function reject(Request $request, DoctorProfile $doctorProfile): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:1000'],
        ]);

        $doctorProfile->update([
            'approval_status' => DoctorProfile::STATUS_REJECTED,
            'review_notes' => $validated['review_notes'],
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return back()->with('success', 'La solicitud médica fue marcada como rechazada.');
    }
}
