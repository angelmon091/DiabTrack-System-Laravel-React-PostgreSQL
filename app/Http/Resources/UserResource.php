<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'initial' => mb_strtoupper(mb_substr($this->name, 0, 1)),
            'isAdmin' => (bool) $this->is_admin,
            'isCurrent' => $request->user()?->is($this->resource) ?? false,
            'roles' => $this->roles->map(fn ($role) => ['id' => $role->id, 'name' => $role->name])->values(),
            'editUrl' => route('admin.users.edit', $this->resource, absolute: false),
            'destroyUrl' => route('admin.users.destroy', $this->resource, absolute: false),
        ];
    }
}
