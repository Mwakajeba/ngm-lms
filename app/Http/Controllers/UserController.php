<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in routes/web.php
    }

    public function index(Request $request)
    {
        $query = \App\Models\User::with(['branch', 'roles']);

        // Optionally filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Optionally filter by role
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->latest()->paginate(20);
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();

        return view('users.index', compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    public function create()
    {
        // Get branches for current company
        $branches = Branch::forCompany()->active()->get();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        
        return view('users.form', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string|max:20|unique:users,phone,NULL,id,company_id,' . current_company_id(),
            'email'        => 'nullable|email|unique:users,email,NULL,id,company_id,' . current_company_id(),
            'password'     => 'required|string|min:8|confirmed',
            'branch_id'    => 'required|exists:branches,id',
            'roles'        => 'required|array|min:1',
            'roles.*'      => 'exists:roles,id',
            'status'       => 'required|in:active,inactive,suspended',
        ]);

        // Get company_id from the selected branch
        $branch = Branch::find($request->branch_id);
        $companyId = $branch->company_id;

        $user = User::create([
            'name'         => $request->name,
            'phone'        => $this->formatPhoneNumber($request->phone),
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'company_id'   => current_company_id(),
            'branch_id'    => $request->branch_id,
            'status'       => $request->status ?? 'active',
            'is_active'    => $request->status === 'active' ? 'yes' : 'no',
        ]);

        // Assign roles
        $roles = Role::whereIn('id', $request->roles)->get();
        $user->assignRole($roles);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Redirect to edit page since we don't have a dedicated show view
        return redirect()->route('users.edit', $user);
    }

    public function edit(User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $branches = Branch::forCompany()->active()->get();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        $user->load('roles');
        
        return view('users.form', compact('user', 'branches', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Custom validation for email to handle existing email
        $emailRules = 'nullable|email';
        if ($request->email !== $user->email) {
            $emailRules .= '|unique:users,email,' . $user->id . ',id,company_id,' . current_company_id();
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string|max:20|unique:users,phone,' . $user->id . ',id,company_id,' . current_company_id(),
            'email'        => $emailRules,
            'password'     => 'nullable|string|min:8|confirmed',
            'branch_id'    => 'required|exists:branches,id',
            'roles'        => 'required|array|min:1',
            'roles.*'      => 'exists:roles,id',
            'status'       => 'required|in:active,inactive,suspended',
        ]);

        // Get company_id from the selected branch
        $branch = Branch::find($request->branch_id);
        $companyId = $branch->company_id;

        $userData = [
            'name'         => $request->name,
            'phone'        => $this->formatPhoneNumber($request->phone),
            'email'        => $request->email,
            'branch_id'    => $request->branch_id,
            'company_id'   => $companyId,
            'status'       => $request->status,
            'is_active'    => $request->status === 'active' ? 'yes' : 'no',
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // Sync roles
        $roles = Role::whereIn('id', $request->roles)->get();
        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent deletion of own account
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }

    public function profile()
    {
        $user = auth()->user();
        $user->load(['branch', 'company', 'roles']);
        
        return view('users.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        // Custom validation for email to handle existing email
        $emailRules = 'nullable|email';
        if ($request->email !== $user->email) {
            $emailRules .= '|unique:users,email,' . $user->id . ',id,company_id,' . current_company_id();
        }
        
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20|unique:users,phone,' . $user->id . ',id,company_id,' . current_company_id(),
            'email'    => $emailRules,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $userData = [
            'name'  => $request->name,
            'phone' => $this->formatPhoneNumber($request->phone),
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('users.profile')->with('success', 'Profile updated successfully!');
    }

    public function changeStatus(User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update([
            'status' => $newStatus,
            'is_active' => $newStatus === 'active' ? 'yes' : 'no'
        ]);

        return redirect()->route('users.index')->with('success', "User status changed to {$newStatus}!");
    }

    public function assignRoles(Request $request, User $user)
    {
        // Ensure user belongs to current company
        if ($user->company_id !== current_company_id()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $roles = Role::whereIn('id', $request->roles)->get();
        $user->syncRoles($roles);

        return redirect()->route('users.edit', $user)->with('success', 'Roles assigned successfully!');
    }

    /**
     * Format phone number to 255 format
     *
     * @param string $phone
     * @return string
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any spaces, dashes, or other characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If starts with +255, remove the +
        if (str_starts_with($phone, '+255')) {
            return substr($phone, 1);
        }
        
        // If starts with 0, remove 0 and add 255
        if (str_starts_with($phone, '0')) {
            return '255' . substr($phone, 1);
        }
        
        // If already starts with 255, return as is
        if (str_starts_with($phone, '255')) {
            return $phone;
        }
        
        // If it's a 9-digit number (Tanzania mobile), add 255
        if (strlen($phone) === 9) {
            return '255' . $phone;
        }
        
        // Return as is if no pattern matches
        return $phone;
    }
}