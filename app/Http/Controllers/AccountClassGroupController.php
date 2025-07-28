<?php

namespace App\Http\Controllers;

use App\Models\AccountClass;
use App\Models\AccountClassGroup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountClassGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $accountClassGroups = AccountClassGroup::with('accountClass')->paginate(10);
        return view('account-class-groups.index', compact('accountClassGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $accountClasses = AccountClass::all();
        return view('account-class-groups.create', compact('accountClasses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:account_class,id',
            'group_code' => 'nullable|string|max:255|unique:account_class_groups,group_code',
            'name' => 'required|string|max:255|unique:account_class_groups,name',
        ]);

        AccountClassGroup::create($request->all());

        return redirect()->route('accounting.fsli-accounts')
            ->with('success', 'Account Class Group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountClassGroup $accountClassGroup): View
    {
        $accountClassGroup->load('accountClass');
        return view('account-class-groups.show', compact('accountClassGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountClassGroup $accountClassGroup): View
    {
        $accountClasses = AccountClass::all();
        return view('account-class-groups.edit', compact('accountClassGroup', 'accountClasses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountClassGroup $accountClassGroup): RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:account_class,id',
            'group_code' => 'nullable|string|max:255|unique:account_class_groups,group_code,' . $accountClassGroup->id,
            'name' => 'required|string|max:255|unique:account_class_groups,name,' . $accountClassGroup->id,
        ]);

        $accountClassGroup->update($request->all());

        return redirect()->route('accounting.fsli-accounts')
            ->with('success', 'Account Class Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountClassGroup $accountClassGroup): RedirectResponse
    {
        $accountClassGroup->delete();

        return redirect()->route('accounting.fsli-accounts')
            ->with('success', 'Account Class Group deleted successfully.');
    }
}
