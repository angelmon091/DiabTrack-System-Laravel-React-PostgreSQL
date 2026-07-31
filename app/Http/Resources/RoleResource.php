<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'usersCount' => $this->users_count,
            'createdAt' => $this->created_at->translatedFormat('d M, Y'),
            'editUrl' => route('admin.roles.edit', $this->resource, absolute: false),
            'destroyUrl' => route('admin.roles.destroy', $this->resource, absolute: false),
        ];
    }
}
