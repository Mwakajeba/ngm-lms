<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;

class ChangeBranchController extends Controller
{
    public function show()
    {
    $user = Auth::user();
    $branches = $user->branches()->get();
    \Log::info('User branches', ['user_id' => $user->id, 'branches' => $branches]);
    return view('auth.change-branch', compact('branches'));
    }

    public function change(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);
        $user->branch_id = $request->branch_id;
        $user->save();
        // Store selected branch in session for filtering
        session(['branch_id' => $request->branch_id]);
        return redirect()->route('dashboard')->with('success', 'Branch changed successfully!');
    }
}
