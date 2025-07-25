<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FsliAccountController extends Controller
{
    public function index()
    {
        return view('accounting.fsli-accounts.index');
    }

    public function create()
    {
        return view('accounting.fsli-accounts.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement FSLI account creation logic
        return redirect()->route('accounting.fsli-accounts.index')->with('success', 'FSLI Account created successfully!');
    }

    public function show($id)
    {
        return view('accounting.fsli-accounts.show', compact('id'));
    }

    public function edit($id)
    {
        return view('accounting.fsli-accounts.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement FSLI account update logic
        return redirect()->route('accounting.fsli-accounts.index')->with('success', 'FSLI Account updated successfully!');
    }

    public function destroy($id)
    {
        // TODO: Implement FSLI account deletion logic
        return redirect()->route('accounting.fsli-accounts.index')->with('success', 'FSLI Account deleted successfully!');
    }
} 