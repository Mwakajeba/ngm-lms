<?php
// This controller will provide group loans data for DataTables AJAX
namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupLoanAjaxController extends Controller
{
    public function index(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);
        $memberIds = $group->members->pluck('customer_id');
        $loans = Loan::with(['customer', 'repayments'])
            ->whereIn('customer_id', $memberIds)
            ->get();

        $data = $loans->map(function($loan) {
            $totalPaid = $loan->repayments->sum(function($r) {
                return $r->principal + $r->interest + $r->penalt_amount + $r->fee_amount;
            });
            $amountWithInterest = $loan->amount + ($loan->schedule->sum('interest') ?? 0);
            $outstanding = $amountWithInterest - $totalPaid;
            $showUrl = route('loans.show', [\Vinkla\Hashids\Facades\Hashids::encode($loan->id)]);
            return [
                'loan_no' => $loan->loanNo ?? $loan->id,
                'customer_no' => $loan->customer->customerNo ?? '',
                'customer' => $loan->customer->name ?? '',
                'amount_with_interest' => number_format($amountWithInterest, 2),
                'total_paid' => number_format($totalPaid, 2),
                'outstanding' => number_format($outstanding, 2),
                'disbursed_on' => $loan->created_at ? $loan->created_at->format('M d, Y') : '',
                'last_repayment_date' => $loan->last_repayment_date ? \Carbon\Carbon::parse($loan->last_repayment_date)->format('M d, Y') : '',
                'show_url' => $showUrl,
            ];
        });
        return response()->json(['data' => $data]);
    }
}
