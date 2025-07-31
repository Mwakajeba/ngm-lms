<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Group;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = Group::with(['loanOfficer', 'branch'])->get();
        return view('groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $loanOfficers = User::whereHas('roles', function ($query) {
            $query->where('name', 'loan-officer');
        })->get();

        $groupLeaders = Customer::all(); // All customer can be group leaders
        $branches = Branch::all();

        return view('groups.create', compact('loanOfficers', 'groupLeaders', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:groups,name',
            'loan_officer' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'minimum_members' => 'required|integer|min:1|max:50',
            'maximum_members' => 'required|integer|min:1|max:100',
            'group_leader' => 'nullable|exists:users,id',
            'meeting_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'meeting_time' => 'nullable|date_format:H:i',
        ], [
            'name.required' => 'Group name is required.',
            'name.unique' => 'A group with this name already exists.',
            'loan_officer.required' => 'Please select a loan officer.',
            'loan_officer.exists' => 'The selected loan officer is invalid.',
            'branch_id.required' => 'Please select a branch.',
            'branch_id.exists' => 'The selected branch is invalid.',
            'minimum_members.required' => 'Minimum members is required.',
            'minimum_members.min' => 'Minimum members must be at least 1.',
            'minimum_members.max' => 'Minimum members cannot exceed 50.',
            'maximum_members.required' => 'Maximum members is required.',
            'maximum_members.min' => 'Maximum members must be at least 1.',
            'maximum_members.max' => 'Maximum members cannot exceed 100.',
            'group_leader.exists' => 'The selected group leader is invalid.',
            'meeting_day.in' => 'Please select a valid meeting day.',
            'meeting_time.date_format' => 'Please enter a valid meeting time.',
        ]);

        // Validate that maximum is greater than minimum
        if ($request->maximum_members <= $request->minimum_members) {
            $validator->errors()->add('maximum_members', 'Maximum members must be greater than minimum members.');
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Group::create([
                'name' => $request->name,
                'loan_officer' => $request->loan_officer,
                'branch_id' => $request->branch_id,
                'minimum_members' => $request->minimum_members,
                'maximum_members' => $request->maximum_members,
                'group_leader' => $request->group_leader,
                'meeting_day' => $request->meeting_day,
                'meeting_time' => $request->meeting_time,
            ]);

            return redirect()->route('groups.index')->with('success', 'Group created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create group. Please try again.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        $group->load(['loanOfficer', 'groupLeader', 'branch', 'members.customer']);
        // TODO: Uncomment when Loan model is properly set up
        // $group->load(['loanOfficer', 'loans']);
        return view('groups.show', compact('group'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        $loanOfficers = User::whereHas('roles', function ($query) {
            $query->where('name', 'loan-officer');
        })->get();

        $groupLeaders = Customer::all(); // All customer can be group leaders
        $branches = Branch::all();

        return view('groups.edit', compact('group', 'loanOfficers', 'groupLeaders', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:groups,name,' . $group->id,
            'loan_officer' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'minimum_members' => 'required|integer|min:1|max:50',
            'maximum_members' => 'required|integer|min:1|max:100',
            'group_leader' => 'nullable|exists:users,id',
            'meeting_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'meeting_time' => 'nullable|date_format:H:i',
        ], [
            'name.required' => 'Group name is required.',
            'name.unique' => 'A group with this name already exists.',
            'loan_officer.required' => 'Please select a loan officer.',
            'loan_officer.exists' => 'The selected loan officer is invalid.',
            'branch_id.required' => 'Please select a branch.',
            'branch_id.exists' => 'The selected branch is invalid.',
            'minimum_members.required' => 'Minimum members is required.',
            'minimum_members.min' => 'Minimum members must be at least 1.',
            'minimum_members.max' => 'Minimum members cannot exceed 50.',
            'maximum_members.required' => 'Maximum members is required.',
            'maximum_members.min' => 'Maximum members must be at least 1.',
            'maximum_members.max' => 'Maximum members cannot exceed 100.',
            'group_leader.exists' => 'The selected group leader is invalid.',
            'meeting_day.in' => 'Please select a valid meeting day.',
            'meeting_time.date_format' => 'Please enter a valid meeting time.',
        ]);

        // Validate that maximum is greater than minimum
        if ($request->maximum_members <= $request->minimum_members) {
            $validator->errors()->add('maximum_members', 'Maximum members must be greater than minimum members.');
        }

        // Validate that minimum is not greater than current member count
        if ($request->minimum_members > $group->current_member_count) {
            $validator->errors()->add('minimum_members', 'Minimum members cannot be greater than current member count (' . $group->current_member_count . ').');
        }

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

            return redirect()->route('groups.index')->with('success', 'Group updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update group. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        try {
            // TODO: Uncomment when Loan model is properly set up
            // Check if group has any loans
            // if ($group->loans()->count() > 0) {
            //     return redirect()->back()->with('error', 'Cannot delete group. It has associated loans.');
            // }

            $group->delete();
            return redirect()->route('groups.index')->with('success', 'Group deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete group. Please try again.');
        }
    }
}
