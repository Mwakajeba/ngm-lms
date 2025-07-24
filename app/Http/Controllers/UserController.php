<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('branch')->latest()->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:255|unique:users',
            'email'    => 'nullable|email|unique:users',
            'password' => 'required|string|min:6',
            'branch_id'=> 'required|exists:branches,id',
            'role'     => 'required|in:admin,manager,staff',
            'is_active'=> 'required|in:yes,no',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'branch_id'=> $request->branch_id,
            'role'     => $request->role,
            'is_active'=> $request->is_active,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $branches = Branch::all();
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:255|unique:users,phone,' . $user->id,
            'email'    => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'branch_id'=> 'required|exists:branches,id',
            'role'     => 'required|in:admin,manager,staff',
            'is_active'=> 'required|in:yes,no',
        ]);

        $user->update([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'branch_id'=> $request->branch_id,
            'role'     => $request->role,
            'is_active'=> $request->is_active,
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}