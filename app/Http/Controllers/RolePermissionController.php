<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->get();
        $permissions = Permission::all();
        $activeUsers = User::where('status', 'active')->count();
        $systemRoles = Role::whereIn('name', ['super-admin', 'admin', 'manager', 'user', 'viewer'])->count();

        // Group permissions by category
        $permissionGroups = $this->groupPermissions($permissions);

        return view('roles.index', compact('roles', 'permissions', 'permissionGroups', 'activeUsers', 'systemRoles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        $permissionGroups = $this->groupPermissions($permissions);

        return view('roles.create', compact('permissions', 'permissionGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => strtolower($request->name),
                'guard_name' => $request->guard_name,
                'description' => $request->description,
            ]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role created successfully!'
                ]);
            }

            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create role: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        $permissionGroups = $this->groupPermissions($role->permissions);

        return view('roles.show', compact('role', 'permissionGroups'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $permissionGroups = $this->groupPermissions($permissions);

        return view('roles.edit', compact('role', 'permissions', 'permissionGroups'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            // Update role basic information
            $role->update([
                'name' => strtolower($request->name),
                'description' => $request->description,
            ]);

            // Handle permissions - if no permissions are selected, sync with empty collection
            if ($request->has('permissions') && is_array($request->permissions)) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
            } else {
                $permissions = collect();
            }

            // Sync permissions (this will add new ones and remove old ones)
            $role->syncPermissions($permissions);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role updated successfully!',
                    'debug' => [
                        'role_id' => $role->id,
                        'permissions_count' => $permissions->count(),
                        'permissions' => $permissions->pluck('name')->toArray()
                    ]
                ]);
            }

            return redirect()->route('roles.index')
                ->with('success', 'Role updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update role: ' . $e->getMessage(),
                    'debug' => [
                        'error' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile()
                    ]
                ], 422);
            }

            return back()->withInput()
                ->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        // Prevent deletion of system roles
        if (in_array($role->name, ['super-admin', 'admin'])) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete system roles.'
                ], 422);
            }
            return back()->with('error', 'Cannot delete system roles.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role that has assigned users.'
                ], 422);
            }
            return back()->with('error', 'Cannot delete role that has assigned users.');
        }

        try {
            $role->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role deleted successfully!'
                ]);
            }

            return redirect()->route('roles.index')
                ->with('success', 'Role deleted successfully!');

        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete role: ' . $e->getMessage()
                ], 422);
            }

            return back()->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }

    public function assignToUser(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        try {
            $roles = Role::whereIn('id', $request->roles)->get();
            $user->syncRoles($roles);

            return back()->with('success', 'Roles assigned to user successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign roles: ' . $e->getMessage());
        }
    }

    public function removeFromUser(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            $role = Role::find($request->role_id);
            $user->removeRole($role);

            return back()->with('success', 'Role removed from user successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to remove role: ' . $e->getMessage());
        }
    }

    public function permissions()
    {
        $permissions = Permission::with('roles')->get();
        $permissionGroups = $this->groupPermissions($permissions);

        return view('roles.permissions', compact('permissions', 'permissionGroups'));
    }

    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'guard_name' => 'required|string|max:255',
            'group' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $permission = Permission::create([
                'name' => strtolower($request->name),
                'guard_name' => $request->guard_name,
                'description' => $request->description,
            ]);

            // Store group information in a custom field or use the name pattern
            // For now, we'll use the group to organize permissions in the UI

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permission created successfully!',
                    'permission' => $permission
                ]);
            }

            return back()->with('success', 'Permission created successfully!');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create permission: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->with('error', 'Failed to create permission: ' . $e->getMessage());
        }
    }

    public function deletePermission(Permission $permission)
    {
        try {
            $permission->delete();
            return back()->with('success', 'Permission deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete permission: ' . $e->getMessage());
        }
    }

    /**
     * Group permissions by category based on their names
     */
    private function groupPermissions($permissions)
    {
        $groups = [
            'user' => [],
            'client' => [],
            'loan' => [],
            'borrower' => [],
            'collection' => [],
            'accounting' => [],
            'savings' => [],
            'report' => [],
            'risk' => [],
            'settings' => [],
            'ai' => [],
            'dashboard' => [],
            'menu' => [],
            'company' => [],
            'branch' => [],
            'system' => [],
        ];

        foreach ($permissions as $permission) {
            $name = strtolower($permission->name);

            if (str_contains($name, 'user') || str_contains($name, 'staff')) {
                $groups['user'][] = $permission;
            } elseif (str_contains($name, 'client')) {
                $groups['client'][] = $permission;
            } elseif (str_contains($name, 'loan')) {
                $groups['loan'][] = $permission;
            } elseif (str_contains($name, 'borrower')) {
                $groups['borrower'][] = $permission;
            } elseif (
                str_contains($name, 'collection') || str_contains($name, 'payment') ||
                str_contains($name, 'receipt') || str_contains($name, 'penalty')
            ) {
                $groups['collection'][] = $permission;
            } elseif (
                str_contains($name, 'accounting') || str_contains($name, 'journal') ||
                str_contains($name, 'bank') || str_contains($name, 'ledger') ||
                str_contains($name, 'financial')
            ) {
                $groups['accounting'][] = $permission;
            } elseif (
                str_contains($name, 'saving') || str_contains($name, 'deposit') ||
                str_contains($name, 'withdrawal')
            ) {
                $groups['savings'][] = $permission;
            } elseif (
                str_contains($name, 'report') || str_contains($name, 'audit') ||
                str_contains($name, 'compliance') || str_contains($name, 'analytics')
            ) {
                $groups['report'][] = $permission;
            } elseif (
                str_contains($name, 'risk') || str_contains($name, 'credit') ||
                str_contains($name, 'collateral') || str_contains($name, 'insurance')
            ) {
                $groups['risk'][] = $permission;
            } elseif (
                str_contains($name, 'setting') || str_contains($name, 'backup') ||
                str_contains($name, 'configuration')
            ) {
                $groups['settings'][] = $permission;
            } elseif (str_contains($name, 'ai') || str_contains($name, 'assistant')) {
                $groups['ai'][] = $permission;
            } elseif (
                str_contains($name, 'dashboard') || str_contains($name, 'statistic') ||
                str_contains($name, 'kpi')
            ) {
                $groups['dashboard'][] = $permission;
            } elseif (str_contains($name, 'menu')) {
                $groups['menu'][] = $permission;
            } elseif (str_contains($name, 'company')) {
                $groups['company'][] = $permission;
            } elseif (str_contains($name, 'branch')) {
                $groups['branch'][] = $permission;
            } else {
                $groups['system'][] = $permission;
            }
        }

        // Remove empty groups
        return array_filter($groups);
    }

    /**
     * Get role statistics for dashboard
     */
    public function getStats()
    {
        $stats = [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_users' => User::count(),
            'system_roles' => Role::whereIn('name', ['super-admin', 'admin', 'manager', 'user', 'viewer'])->count(),
        ];

        return response()->json($stats);
    }

    // Menu Management Methods
    public function manageMenus(Role $role)
    {
        $role->load([
            'menus' => function ($query) {
                $query->with('children');
            }
        ]);

        $allMenus = Menu::with('children')
            ->whereNull('parent_id')
            ->get();

        return view('roles.manage-menus', compact('role', 'allMenus'));
    }

    public function assignMenus(Request $request, Role $role)
    {
        $request->validate([
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:menus,id'
        ]);

        try {
            DB::beginTransaction();

            $role->menus()->sync($request->menu_ids);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Menus assigned successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign menus: ' . $e->getMessage()
            ], 422);
        }
    }

    public function removeMenu(Request $request, Role $role)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id'
        ]);

        try {
            $role->menus()->detach($request->menu_id);

            return response()->json([
                'success' => true,
                'message' => 'Menu removed successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove menu: ' . $e->getMessage()
            ], 422);
        }
    }
}

