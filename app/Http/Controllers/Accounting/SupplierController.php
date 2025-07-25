<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return view('accounting.suppliers.index');
    }

    public function create()
    {
        return view('accounting.suppliers.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement supplier creation logic
        return redirect()->route('accounting.suppliers.index')->with('success', 'Supplier created successfully!');
    }

    public function show($id)
    {
        return view('accounting.suppliers.show', compact('id'));
    }

    public function edit($id)
    {
        return view('accounting.suppliers.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement supplier update logic
        return redirect()->route('accounting.suppliers.index')->with('success', 'Supplier updated successfully!');
    }

    public function destroy($id)
    {
        // TODO: Implement supplier deletion logic
        return redirect()->route('accounting.suppliers.index')->with('success', 'Supplier deleted successfully!');
    }
} 