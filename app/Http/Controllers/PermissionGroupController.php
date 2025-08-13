<?php

namespace App\Http\Controllers;

use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissionGroups = PermissionGroup::withCount('permissions')
            ->ordered()
            ->get();

        return view('permission-groups.index', compact('permissionGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('permission-groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permission_groups,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $permissionGroup = PermissionGroup::create([
                'name' => strtolower($request->name),
                'display_name' => $request->display_name,
                'description' => $request->description,
                'color' => $request->color ?? '#6c757d',
                'icon' => $request->icon,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => true,
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permission group created successfully!',
                    'permission_group' => $permissionGroup
                ]);
            }

            return redirect()->route('permission-groups.index')
                ->with('success', 'Permission group created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create permission group: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->with('error', 'Failed to create permission group: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PermissionGroup $permissionGroup)
    {
        $permissionGroup->load('permissions');
        return view('permission-groups.show', compact('permissionGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PermissionGroup $permissionGroup)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'id' => $permissionGroup->id,
                'name' => $permissionGroup->name,
                'display_name' => $permissionGroup->display_name,
                'description' => $permissionGroup->description,
                'icon' => $permissionGroup->icon,
                'color' => $permissionGroup->color,
                'sort_order' => $permissionGroup->sort_order,
                'is_active' => (bool) $permissionGroup->is_active,
            ]);
        }

        return redirect()->route('permission-groups.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PermissionGroup $permissionGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permission_groups,name,' . $permissionGroup->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $permissionGroup->update([
                'name' => strtolower($request->name),
                'display_name' => $request->display_name,
                'description' => $request->description,
                'color' => $request->color ?? '#6c757d',
                'icon' => $request->icon,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permission group updated successfully!',
                    'permission_group' => $permissionGroup
                ]);
            }

            return redirect()->route('permission-groups.index')
                ->with('success', 'Permission group updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update permission group: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->with('error', 'Failed to update permission group: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PermissionGroup $permissionGroup)
    {
        try {
            // Check if group has permissions
            if ($permissionGroup->permissions()->count() > 0) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete permission group that has assigned permissions.'
                    ], 422);
                }
                return back()->with('error', 'Cannot delete permission group that has assigned permissions.');
            }

            $permissionGroup->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permission group deleted successfully!'
                ]);
            }

            return redirect()->route('permission-groups.index')
                ->with('success', 'Permission group deleted successfully!');

        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete permission group: ' . $e->getMessage()
                ], 422);
            }

            return back()->with('error', 'Failed to delete permission group: ' . $e->getMessage());
        }
    }

    /**
     * Get all permission groups for dropdown/select
     */
    public function getGroups()
    {
        $groups = PermissionGroup::active()
            ->ordered()
            ->get(['id', 'name', 'display_name']);

        return response()->json($groups);
    }
}
