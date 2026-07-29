<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'emailVerified' => $this->user->hasVerifiedEmail(),
            'licenseNumber' => $this->license_number,
            'specialty' => $this->specialty,
            'status' => $this->approval_status,
            'reviewNotes' => $this->review_notes,
            'reviewedBy' => $this->approver?->name,
            'requestedAt' => $this->created_at->format('d/m/Y H:i'),
            'approveUrl' => route('admin.doctors.approve', $this->resource, absolute: false),
            'rejectUrl' => route('admin.doctors.reject', $this->resource, absolute: false),
        ];
    }
}
