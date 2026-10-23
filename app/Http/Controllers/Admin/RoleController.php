<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        abort_unless(Gate::allows('roles.manage'), 403);

        return view('admin.roles.index', [
            'roles' => Role::query()
                ->where('guard_name', 'web')
                ->with('permissions')
                ->orderBy('name')
                ->get(),
            'permissions' => Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(Role $role): View
    {
        abort_unless(Gate::allows('roles.manage'), 403);

        return view('admin.roles.edit', [
            'roleModel' => $role->load('permissions'),
            'permissions' => Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_unless(Gate::allows('roles.manage'), 403);

        $role->syncPermissions($request->validated('permissions', []));

        return redirect()
            ->route('admin.roles.index')
            ->with('status', __('Rôle mis à jour.'));
    }
}
