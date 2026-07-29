<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Clase UserController
 *
 * Controlador para la gestión de usuarios.
 * Permite crear, editar, eliminar y listar usuarios.
 */
class UserController extends Controller
{
    /**
     * Muestra un listado de los usuarios.
     *
     * @return View
     */
    public function index(Request $request): InertiaResponse
    {
        $search = $request->input('search');

        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Users/Index', [
            'users' => UserResource::collection($users),
            'filters' => ['search' => $search],
            'createUrl' => route('admin.users.create', absolute: false),
            'indexUrl' => route('admin.users.index', absolute: false),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return View
     */
    public function create(): InertiaResponse
    {
        $roles = Role::all();

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
            ])->values(),
            'storeUrl' => route('admin.users.store', absolute: false),
            'indexUrl' => route('admin.users.index', absolute: false),
        ]);
    }

    /**
     * Almacena un nuevo usuario en el almacenamiento.
     *
     * @return RedirectResponse
     */
    public function store(AdminUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Automagically hashed by model cast
        ]);

        $user->is_admin = $request->has('is_admin');
        $user->save();

        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     *
     * @return View
     */
    public function edit(User $user): InertiaResponse
    {
        $roles = Role::all();
        $user->load('roles');

        return Inertia::render('Admin/Users/Edit', [
            'user' => UserResource::make($user),
            'roles' => $roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
            ])->values(),
            'updateUrl' => route('admin.users.update', $user, absolute: false),
            'indexUrl' => route('admin.users.index', absolute: false),
        ]);
    }

    /**
     * Actualiza un usuario existente en el almacenamiento.
     *
     * @return RedirectResponse
     */
    public function update(AdminUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->fill($updateData);

        // Prevenir que el administrador se revoque a sí mismo los privilegios por accidente
        if ($user->id === auth()->id() && ! $request->has('is_admin')) {
            $user->is_admin = true;
        } else {
            $user->is_admin = $request->has('is_admin');
        }

        $user->save();

        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario del almacenamiento.
     *
     * @return RedirectResponse
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
