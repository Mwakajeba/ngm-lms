<?php
namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class GroupMemberAjaxController extends Controller
{
    public function index(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);

        // Get group members with their customer data through the pivot table
        $members = $group->members()->withPivot(['joined_date', 'notes'])->with([
            'loans' => function ($query) {
                $query->where('status', 'active');
            }
        ])->get();

        $data = $members->map(function ($customer) use ($group) {
            $hasActiveLoan = $customer->loans->count() > 0;
            return [
                'member' => '<div class="d-flex align-items-center"><div class="avatar-sm bg-light-primary rounded-circle d-flex align-items-center justify-content-center me-2"><i class="bx bx-user font-size-16"></i></div><div><strong>' . e($customer->name) . '</strong><br><small class="text-muted">' . e($customer->phone1 ?? 'No phone') . '</small></div></div>',
                'joined_date' => $customer->pivot->joined_date ? \Carbon\Carbon::parse($customer->pivot->joined_date)->format('M d, Y') : 'N/A',
                'notes' => '<small class="text-muted">' . e(\Str::limit($customer->pivot->notes ?? '', 50)) . '</small>',
                'actions' => '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMember(\'' . Hashids::encode($group->id) . '\',' . $customer->id . ',\'' . e($customer->name) . '\')" title="Remove Member" ' . ($hasActiveLoan ? 'disabled' : '') . '><i class="bx bx-trash"></i></button>'
            ];
        });

        return response()->json(['data' => $data]);
    }
}
