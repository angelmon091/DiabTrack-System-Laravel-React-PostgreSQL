<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Clase RoleController
 *
 * Gestiona las operaciones CRUD para los roles del sistema.
 * Permite crear, editar, listar y eliminar roles de usuario.
 */
class RoleController extends Controller
{
    /**
     * Muestra un listado de los roles existentes.
     *
     * @return View
     */
    public function index(): InertiaResponse
    {
        $roles = Role::withCount('users')->latest()->paginate(10);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => RoleResource::collection($roles),
            'createUrl' => route('admin.roles.create', absolute: false),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo rol.
     *
     * @return View
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('Admin/Roles/Create', [
            'storeUrl' => route('admin.roles.store', absolute: false),
            'indexUrl' => route('admin.roles.index', absolute: false),
        ]);
    }

    /**
     * Almacena un nuevo rol en la base de datos.
     *
     * @return RedirectResponse
     */
    public function store(AdminRoleRequest $request)
    {
        Role::create($request->validated());

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un rol existente.
     *
     * @return View
     */
    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Actualiza un rol existente en la base de datos.
     *
     * @return RedirectResponse
     */
    public function update(AdminRoleRequest $request, Role $role)
    {
        $role->update($request->validated());

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Elimina un rol de la base de datos.
     *
     * @return RedirectResponse
     */
    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado exitosamente.');
    }
}
