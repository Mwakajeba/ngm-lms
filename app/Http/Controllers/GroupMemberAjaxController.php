<?php
namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class GroupMemberAjaxController extends Controller
{
    public function index(Request $request, $groupId)
    {
        $group = Group::with(['members.customer.loans'])->findOrFail($groupId);
        $members = $group->members;
        $data = $members->map(function($member) use ($group) {
            $hasActiveLoan = $member->customer->loans()->where('status', 'active')->exists();
            return [
                'member' => '<div class="d-flex align-items-center"><div class="avatar-sm bg-light-primary rounded-circle d-flex align-items-center justify-content-center me-2"><i class="bx bx-user font-size-16"></i></div><div><strong>' . e($member->customer->name) . '</strong><br><small class="text-muted">' . e($member->customer->phone ?? 'No phone') . '</small></div></div>',
                'joined_date' => $member->joined_date ? $member->joined_date->format('M d, Y') : '',
                'notes' => '<small class="text-muted">' . e(\Str::limit($member->notes, 50)) . '</small>',
                'actions' => '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMember(\'' . Hashids::encode($group->id) . '\',' . $member->id . ',\'' . e($member->customer->name) . '\')" title="Remove Member" ' . ($hasActiveLoan ? 'disabled' : '') . '><i class="bx bx-trash"></i></button>'
            ];
        });
        return response()->json(['data' => $data]);
    }
}
