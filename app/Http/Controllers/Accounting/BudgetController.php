<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Branch;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Budget::with(['user', 'branch', 'company', 'budgetLines']);

        // Filter by company scope
        if ($user->company_id) {
            $query->byCompany($user->company_id);
        }

        // Filter by branch scope
        if ($user->branch_id) {
            $query->byBranch($user->branch_id);
        }

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('year', 'like', "%{$search}%");
            });
        }

        if ($request->filled('year')) {
            $query->byYear($request->year);
        }

        $budgets = $query->orderBy('created_at', 'desc')->paginate(15);

        info('Budgets retrieved successfully', ['budgets_count' => $budgets->total()]);

        return view('budgets.index', compact('budgets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $accounts = ChartAccount::whereHas('accountClassGroup', function ($query) {
            $query->where('company_id', Auth::user()->company_id);
        })->get();

        return view('budgets.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2020|max:2030',
            'description' => 'nullable|string|max:1000',
            'budget_lines' => 'required|array|min:1',
            'budget_lines.*.account_id' => 'required|exists:chart_accounts,id',
            'budget_lines.*.amount' => 'required|numeric|min:0',
            'budget_lines.*.category' => 'required|in:Revenue,Expense,Capital Expenditure',
        ]);

        try {
            DB::beginTransaction();

            $budget = Budget::create([
                'name' => $request->name,
                'year' => $request->year,
                'description' => $request->description,
                'user_id' => Auth::id(),
                'branch_id' => Auth::user()->branch_id,
                'company_id' => Auth::user()->company_id,
            ]);

            // Create budget lines
            foreach ($request->budget_lines as $line) {
                $budget->budgetLines()->create([
                    'account_id' => $line['account_id'],
                    'amount' => $line['amount'],
                    'category' => $line['category'],
                ]);
            }

            DB::commit();

            return redirect()->route('accounting.budgets.index')
                ->with('success', 'Budget created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create budget: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        // Ensure user can only access budgets from their branch
        if ($budget->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only access budgets from your own branch.');
        }
        
        $budget->load(['user', 'branch', 'company', 'budgetLines.account']);
        
        return view('budgets.show', compact('budget'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Budget $budget)
    {
        // Ensure user can only edit budgets from their branch
        if ($budget->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only edit budgets from your own branch.');
        }
        
        $accounts = ChartAccount::whereHas('accountClassGroup', function ($query) {
            $query->where('company_id', Auth::user()->company_id);
        })->get();

        $budget->load('budgetLines');

        return view('budgets.edit', compact('budget', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        // Ensure user can only update budgets from their branch
        if ($budget->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only update budgets from your own branch.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:2020|max:2030',
            'description' => 'nullable|string|max:1000',
            'budget_lines' => 'required|array|min:1',
            'budget_lines.*.account_id' => 'required|exists:chart_accounts,id',
            'budget_lines.*.amount' => 'required|numeric|min:0',
            'budget_lines.*.category' => 'required|in:Revenue,Expense,Capital Expenditure',
        ]);

        try {
            DB::beginTransaction();

            $budget->update([
                'name' => $request->name,
                'year' => $request->year,
                'description' => $request->description,
                'branch_id' => Auth::user()->branch_id,
            ]);

            // Delete existing budget lines
            $budget->budgetLines()->delete();

            // Create new budget lines
            foreach ($request->budget_lines as $line) {
                $budget->budgetLines()->create([
                    'account_id' => $line['account_id'],
                    'amount' => $line['amount'],
                    'category' => $line['category'],
                ]);
            }

            DB::commit();

            return redirect()->route('accounting.budgets.index')
                ->with('success', 'Budget updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update budget: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        // Ensure user can only delete budgets from their branch
        if ($budget->branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only delete budgets from your own branch.');
        }
        
        try {
            $budget->delete();
            return redirect()->route('accounting.budgets.index')
                ->with('success', 'Budget deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete budget: ' . $e->getMessage());
        }
    }
} 