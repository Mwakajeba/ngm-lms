<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index()
    {

        return view('loans.index');
    }

    public function listLoans()
    {
        $branchId = auth()->user()->branch_id;
        $loans = \App\Models\Loan::with('customer', 'product', 'branch')
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->latest()->get();

        return view('loans.list', compact('loans'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::all();
        $groups = \App\Models\Group::all();
        $products = \App\Models\LoanProduct::all();
        $sectors = ['Agriculture', 'Business', 'Education', 'Health', 'Other']; // Example sectors
        return view('loans.create', compact('customers', 'groups', 'products', 'sectors'));
    }
}
