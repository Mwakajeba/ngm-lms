<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\ChartAccount;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function index()
    {
        $journals = Journal::with('items')->latest()->get();
        return view('accounting.journals.index', compact('journals'));
    }

    public function create()
    {
        $branches = Branch::all();
        $customers = Customer::all();
        $accounts = ChartAccount::all();
        return view('accounting.journals.create', compact('accounts','customers', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'items' => 'required|array|min:2', // Must have both debit and credit
            'items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.nature' => 'required|in:debit,credit',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        DB::transaction(function () use ($request) {
            $journal = Journal::create([
                'date' => $request->date,
                'branch_id' => Auth::user()->branch_id,
                'customer_id' => $request->customer_id,
                'reference_type' => 'Journal',
            ]);

            // Update reference to journal id
            $journal->reference = $journal->id;
            $journal->save();

            foreach ($request->items as $item) {
                $journal->items()->create($item);
            }
        });

        return redirect()->route('accounting.journals.index')->with('success', 'Journal entry created.');
    }

    public function show(Journal $journal)
    {
        $journal->load('items.chartAccount');
        return view('accounting.journals.show', compact('journal'));
    }

    public function edit(Journal $journal)
    {
        $chartAccounts = ChartAccount::all();
        $journal->load('items');
        return view('accounting.journals.edit', compact('journal', 'chartAccounts'));
    }

    public function update(Request $request, Journal $journal)
    {
        $request->validate([
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'items' => 'required|array|min:2',
            'items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.nature' => 'required|in:debit,credit',
        ]);

        DB::transaction(function () use ($request, $journal) {
            $journal->update([
                'date' => $request->date,
                'reference' => $request->reference,
            ]);

            $journal->items()->delete(); // Remove old items
            foreach ($request->items as $item) {
                $journal->items()->create($item);
            }
        });

        return redirect()->route('accounting.journals.index')->with('success', 'Journal entry updated.');
    }

    public function destroy(Journal $journal)
    {
        $journal->delete();
        return redirect()->route('accounting.journals.index')->with('success', 'Journal entry deleted.');
    }
}

