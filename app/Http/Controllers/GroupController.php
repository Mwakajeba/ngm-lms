<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\Branch;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $groups = Group::with(['loanOfficer', 'branch'])
            ->where('branch_id', $branchId)
            ->get();
        return view('groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $loanOfficers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['loan-officer', 'admin']);
        })->get();


        $branchId = auth()->user()->branch_id;
        // Only customers in 'Borrower' category can be group leaders
        $groupLeaders = Customer::where('branch_id', $branchId)
            ->where('category', 'Borrower')->get();

        return view('groups.create', compact('loanOfficers', 'groupLeaders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255|unique:groups,name',
        'loan_officer' => 'required|exists:users,id',
        'minimum_members' => 'nullable|integer|min:1|max:1000000',
        'maximum_members' => 'nullable|integer|min:1|max:1000000',
        'group_leader' => [
            'nullable',
            'exists:customers,id',
            function ($attribute, $value, $fail) {
                if ($value) {
                    $customer = Customer::find($value);
                    if (!$customer || $customer->category !== 'Borrower') {
                        $fail('The selected group leader must be a customer in the Borrower category.');
                    }
                }
            }
        ],
        'meeting_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday,every_day,every_week,every_month',
        'meeting_time' => 'nullable|date_format:H:i',
    ], [
        'name.required' => 'Group name is required.',
        'name.unique' => 'A group with this name already exists.',
        'loan_officer.required' => 'Please select a loan officer.',
        'loan_officer.exists' => 'The selected loan officer is invalid.',
        'group_leader.exists' => 'The selected group leader is invalid.',
        'meeting_day.in' => 'Please select a valid meeting day.',
        'meeting_time.date_format' => 'Please enter a valid meeting time.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
        DB::beginTransaction();

        $group = Group::create([
            'name' => $request->name,
            'loan_officer' => $request->loan_officer,
            'branch_id' => Auth::user()->branch_id,
            'minimum_members' => $request->minimum_members,
            'maximum_members' => $request->maximum_members,
            'group_leader' => $request->group_leader,
            'meeting_day' => $request->meeting_day,
            'meeting_time' => $request->meeting_time,
        ]);

        // Only create a GroupMember if a group leader was provided and is valid
        if ($request->filled('group_leader')) {
            GroupMember::create([
                'group_id' => $group->id,
                'customer_id' => $request->group_leader,
                'joined_date' => now()->format('Y M D')
            ]);
        }

        DB::commit();

        return redirect()->route('groups.index')->with('success', 'Group created successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Group creation failed: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to create group. Please try again.')->withInput();
    }
}
    /**
     * Display the specified resource.
     */
    public function show($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        $group->load(['loanOfficer', 'groupLeader', 'branch', 'members.customer']);

        // Get all loans for this group (assuming each member has loans)
        $memberIds = $group->members->pluck('customer_id');
        $loans = Loan::whereIn('customer_id', $memberIds)->get();

        return view('groups.show', compact('group', 'loans'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        // Decode the ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        $loanOfficers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['loan-officer', 'admin']);
        })->get();

        $branchId = auth()->user()->branch_id;
        // Only customers in 'Borrower' category can be group leaders
        $groupLeaders = Customer::where('branch_id', $branchId)
            ->where('category', 'Borrower')->get();

        return view('groups.edit', compact('group', 'loanOfficers', 'groupLeaders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        // Decode group ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:groups,name,' . $group->id,
            'loan_officer' => 'required|exists:users,id',
            'minimum_members' => 'nullable|integer|min:1|max:1000000',
            'maximum_members' => 'nullable|integer|min:1|max:1000000',
            'group_leader' => [
                'nullable',
                'exists:customers,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $customer = Customer::find($value);
                        if (!$customer || $customer->category !== 'Borrower') {
                            $fail('The selected group leader must be a customer in the Borrower category.');
                        }
                    }
                }
            ],
            'meeting_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday,every_month,every_day,every_week',
            'meeting_time' => 'nullable|date_format:H:i',
        ], [
            'name.required' => 'Group name is required.',
            'name.unique' => 'A group with this name already exists.',
            'loan_officer.required' => 'Please select a loan officer.',
            'loan_officer.exists' => 'The selected loan officer is invalid.',
            'group_leader.exists' => 'The selected group leader is invalid.',
            'meeting_day.in' => 'Please select a valid meeting day.',
            'meeting_time.date_format' => 'Please enter a valid meeting time.',
        ]);



        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $group->update([
                'name' => $request->name,
                'loan_officer' => $request->loan_officer,
                'branch_id' => $request->branch_id,
                'minimum_members' => $request->minimum_members,
                'maximum_members' => $request->maximum_members,
                'group_leader' => $request->group_leader,
                'meeting_day' => $request->meeting_day,
                'meeting_time' => $request->meeting_time,
            ]);

            // Optionally, if you allow updating group members elsewhere, ensure only Borrowers are added
            // Example: (pseudo-code, adapt as needed)
            // foreach ($request->members as $memberId) {
            //     $member = Customer::where('id', $memberId)->where('category', 'Borrower')->first();
            //     if ($member) { /* add to group */ }
            // }

            return redirect()->route('groups.index')->with('success', 'Group updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update group. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($encodedId)
    {
        // Decode the encoded ID
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return redirect()->route('groups.index')->withErrors(['Group not found.']);
        }

        $group = Group::findOrFail($decoded[0]);

        try {
            if ($group->loans()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete group. It has associated loans.');
            }
            if ($group->members()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete group. It has associated members.');
            }

            $group->delete();
            return redirect()->route('groups.index')->with('success', 'Group deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete group. Please try again.');
        }
    }
}
