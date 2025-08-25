<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('incs.navBar', function ($view) {
            $user = \Auth::user();
            $branchIds = [];
            // Check if user has selected a branch (change branch)
            $selectedBranchId = session('branch_id');
            if ($selectedBranchId) {
                $branchIds = [$selectedBranchId];
            } elseif ($user) {
                // If user has many branches
                if (method_exists($user, 'branches')) {
                    $branchIds = $user->branches()->pluck('branches.id')->toArray();
                }
                // If user has single branch
                elseif (isset($user->branch_id)) {
                    $branchIds = [$user->branch_id];
                }
            }
            $today = now();
            $arrearsLoans = collect();
            $loansQuery = \DB::table('loans')
                ->join('customers', 'loans.customer_id', '=', 'customers.id')
                ->where('loans.status', 'active');
            if (!empty($branchIds)) {
                $loansQuery->whereIn('loans.branch_id', $branchIds);
            }
            $loans = $loansQuery
                ->select('loans.id as loan_id', 'customers.name as customer_name', 'customers.customerNo as customer_no', 'loans.loanNo', 'loans.branch_id')
                ->get();

            foreach ($loans as $loan) {
                $schedules = \DB::table('loan_schedules')
                    ->where('loan_id', $loan->loan_id)
                    ->whereDate('due_date', '<', $today)
                    ->get();

                $maxDays = 0;
                $totalArrears = 0;
                foreach ($schedules as $schedule) {
                    $days = \Carbon\Carbon::parse($schedule->due_date)->diffInDays($today);
                    if ($days >= 1 && $days <= 30) {
                        // Get repayments for this schedule
                        $repayments = \DB::table('repayments')
                            ->where('loan_id', $loan->loan_id)
                            ->whereDate('payment_date', '>=', $schedule->due_date)
                            ->sum(\DB::raw('principal + interest'));
                        $overdueAmount = ($schedule->principal + $schedule->interest) - $repayments;
                        if ($overdueAmount > 0) {
                            $totalArrears += $overdueAmount;
                            if ($days > $maxDays) $maxDays = $days;
                        }
                    }
                }
                if ($totalArrears > 0 && $maxDays >= 1 && $maxDays <= 30) {
                    $arrearsLoans->push((object)[
                        'loan_id' => $loan->loan_id,
                        'customer_name' => $loan->customer_name,
                        'customer_no' => $loan->customer_no,
                        'loanNo' => $loan->loanNo,
                        'amount_in_arrears' => $totalArrears,
                        'days_in_arrears' => round($maxDays),
                    ]);
                }
            }
            $view->with('arrearsLoans', $arrearsLoans);
            $view->with('arrearsLoansCount', $arrearsLoans->count());
        });

        View::composer('incs.sideMenu', function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with('menus', []);
                return;
            }

            // Get all user roles
            $userRoles = $user->roles;

            if ($userRoles->isEmpty()) {
                $view->with('menus', []);
                return;
            }

            // Get role IDs
            $roleIds = $userRoles->pluck('id')->toArray();

            // Get menus for all user roles
            $menus = Menu::with('children')
                ->whereNull('parent_id')
                ->whereHas('roles', function ($query) use ($roleIds) {
                    $query->whereIn('roles.id', $roleIds);
                })
                ->get();

            $view->with('menus', $menus);
        });
    }
}
