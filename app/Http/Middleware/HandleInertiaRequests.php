<?php

namespace App\Http\Middleware;

use App\Models\PatientNotification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        if ($user) {
            $user->loadMissing('roles');
        }

        $roleNames = $user?->roles->pluck('name')->values() ?? collect();
        $esPaciente = $roleNames->contains('paciente');
        $esCuidador = $roleNames->contains('cuidador');
        $esMedico = $roleNames->contains('médico');

        if ($esMedico) {
            $user->loadMissing('doctorProfile');
        }

        $notifications = $user
            ? PatientNotification::where('user_id', $user->id)->latest()->take(8)->get()
            : collect();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'emailVerifiedAt' => $user->email_verified_at?->toISOString(),
                ] : null,
                'roles' => $user?->roles->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                ])->values()->all() ?? [],
                'permissions' => [
                    'esAdmin' => $user?->isAdmin() ?? false,
                    'puedeVerVitales' => $esPaciente || $esCuidador || $esMedico,
                    'puedeVincularPacientes' => $esCuidador
                        || ($esMedico && ($user->doctorProfile?->isApproved() ?? false)),
                    'puedeBuscar' => $esPaciente,
                ],
            ],
            'navigation' => $user ? [
                'dashboardUrl' => route('dashboard', absolute: false),
                'profileUrl' => route('profile.edit', absolute: false),
                'logoutUrl' => route('logout', absolute: false),
                'searchUrl' => $esPaciente ? route('search', absolute: false) : null,
                'notificationsReadAllUrl' => route('notifications.read-all', absolute: false),
                'notificationsDestroyAllUrl' => route('notifications.destroy-all', absolute: false),
            ] : null,
            'notifications' => $notifications->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'type' => $notification->type,
                'read' => $notification->read_at !== null,
                'createdAt' => $notification->created_at->diffForHumans(),
                'readUrl' => route('notifications.read', $notification, absolute: false),
                'destroyUrl' => route('notifications.destroy', $notification, absolute: false),
            ])->values()->all(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}
