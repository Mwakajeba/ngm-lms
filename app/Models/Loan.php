<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Optional if you want soft deletes

class Loan extends Model
{
    // Uncomment if using soft deletes
    // use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'group_id',
        'product_id',
        'amount',
        'interest',
        'interest_amount',
        'period',
        'amount_total',
        'bank_account_id',
        'date_applied',
        'disbursed_on',
        'status',
        'sector',
        'interest_cycle',
        'loan_officer_id',
        'loanNo',
        'top_up_id',
        'first_repayment_date',
        'last_repayment_date',
        'branch_id',
    ];

    // Loan status constants
    const STATUS_APPLIED = 'applied';
    const STATUS_CHECKED = 'checked';
    const STATUS_APPROVED = 'approved';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_ACTIVE = 'active';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DEFAULTED = 'defaulted';



    protected static function boot()
    {
        parent::boot();

        static::creating(function ($loan) {
            // Namba ya mwanzo unayotaka kuanzia
            $startNumber = 1000000;

            do {
                $loanNumber = 'SF-' . ($startNumber + self::count());
            } while (self::where('loanNo', $loanNumber)->exists());

            $loan->loanNo = $loanNumber;
        });
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function product()
    {
        return $this->belongsTo(LoanProduct::class, 'product_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function topUpLoan()
    {
        return $this->belongsTo(Loan::class, 'top_up_id');
    }

    public function topUpChildren()
    {
        return $this->hasMany(Loan::class, 'top_up_id');
    }

    public function schedule()
    {
        return $this->hasMany(LoanSchedule::class, 'loan_id');
    }

    public function repayments()
    {
        return $this->hasMany(Repayment::class, 'loan_id');
    }

    public function loanFiles()
    {
        return $this->hasMany(LoanFile::class, 'loan_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function guarantors()
    {
        return $this->belongsToMany(Customer::class, 'loan_guarantor')
            ->withPivot('relation')
            ->withTimestamps();
    }

    public function loanOfficer(){
        return $this->belongsTo(User::class);
    }

    // New approval relationships
    public function approvals()
    {
        return $this->hasMany(LoanApproval::class);
    }

    public function currentApproval()
    {
        return $this->approvals()->latest()->first();
    }

    // Dynamic approval methods based on roles
    public function getApprovalRoles()
    {
        if (!$this->product || !$this->product->has_approval_levels) {
            return [];
        }

        $roles = explode(',', $this->product->approval_levels);
        $filteredRoles = array_filter($roles); // Remove empty values

        // Convert to integers for proper comparison
        return array_map('intval', $filteredRoles);
    }

    public function getCurrentApprovalLevel()
    {
        $lastApproval = $this->currentApproval();
        return $lastApproval ? $lastApproval->approval_level : 0;
    }

    public function getNextApprovalLevel()
    {
        $approvalRoles = $this->getApprovalRoles();
        if (empty($approvalRoles)) {
            return null;
        }

        $currentLevel = $this->getCurrentApprovalLevel();
        return $currentLevel < count($approvalRoles) ? $currentLevel + 1 : null;
    }

    public function getRequiredApprovalLevels()
    {
        return $this->getApprovalRoles();
    }

    public function getNextApprovalRole()
    {
        $approvalRoles = $this->getApprovalRoles();
        $nextLevel = $this->getNextApprovalLevel();

        if (!$nextLevel || $nextLevel > count($approvalRoles)) {
            return null;
        }

        return $approvalRoles[$nextLevel - 1];
    }

    public function canBeApprovedByUser($user)
    {
        $nextRoleId = $this->getNextApprovalRole();
        if (!$nextRoleId) {
            return false;
        }

        $userRoles = $user->roles->pluck('id')->toArray();
        return in_array($nextRoleId, $userRoles);
    }

    public function hasUserApproved($user)
    {
        return $this->approvals()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function canBeRejected()
    {
        $rejectableStatuses = [self::STATUS_APPLIED, self::STATUS_CHECKED, self::STATUS_APPROVED];
        return in_array($this->status, $rejectableStatuses);
    }

    public function isFullyApproved()
    {
        $approvalRoles = $this->getApprovalRoles();
        if (empty($approvalRoles)) {
            return false;
        }

        $requiredLevels = count($approvalRoles);
        $approvedLevels = $this->approvals()->where('action', '!=', 'rejected')->count();

        return $approvedLevels >= $requiredLevels;
    }

    public function isReadyForDisbursement()
    {
        $approvalRoles = $this->getApprovalRoles();
        if (empty($approvalRoles)) {
            return $this->status === self::STATUS_ACTIVE;
        }

        // Check if all levels except the last (accountant) are approved
        $requiredLevels = count($approvalRoles);
        $approvedLevels = $this->approvals()->where('action', '!=', 'rejected')->count();

        return $approvedLevels >= ($requiredLevels - 1); // All except accountant
    }

    public function getApprovalStatus()
    {
        if ($this->status === self::STATUS_REJECTED) {
            return 'rejected';
        }

        if ($this->status === self::STATUS_ACTIVE) {
            return 'disbursed';
        }

        $approvalRoles = $this->getApprovalRoles();
        if (empty($approvalRoles)) {
            return $this->status;
        }

        $currentLevel = $this->getCurrentApprovalLevel();
        $totalLevels = count($approvalRoles);

        if ($currentLevel === 0) {
            return 'pending_first_approval';
        }

        if ($currentLevel < $totalLevels) {
            $roleName = $this->getRoleNameById($approvalRoles[$currentLevel]);
            return "pending_{$roleName}_approval";
        }

        return 'fully_approved';
    }

    public function getRoleNameById($roleId)
    {
        $role = \App\Models\Role::find($roleId);
        return $role ? strtolower(str_replace(' ', '_', $role->name)) : 'unknown';
    }

    public function getNextApprovalAction()
    {
        $approvalRoles = $this->getApprovalRoles();
        $nextLevel = $this->getNextApprovalLevel();

        if (!$nextLevel) {
            return null;
        }

        $roleId = $approvalRoles[$nextLevel - 1];
        $role = \App\Models\Role::find($roleId);

        if (!$role) {
            return null;
        }

        // Check if this is the accountant (last role)
        if ($nextLevel === count($approvalRoles)) {
            return 'disburse';
        }

        // For other roles, determine action based on level
        switch ($nextLevel) {
            case 1:
                return 'check';
            case 2:
                return 'approve';
            case 3:
                return 'authorize';
            default:
                return 'approve';
        }
    }

    public function getApprovalLevelName($level)
    {
        $approvalRoles = $this->getApprovalRoles();
        if (!isset($approvalRoles[$level - 1])) {
            return 'Unknown';
        }

        $roleId = $approvalRoles[$level - 1];
        $role = \App\Models\Role::find($roleId);

        return $role ? $role->name : 'Unknown';
    }


    public function calculateInterestAmount(float $rate = null, bool $returnSchedule = false): float|array
    {
        $product = $this->product;
        if (!$product)
            return $returnSchedule ? [] : 0;

        $principal = $this->amount;
        $rate = $rate ?? $this->interest ?? $product->interest ?? 0;

        $period = $this->period;
        $method = $product->interest_method ?? 'flat_rate';

        $ratePerPeriod = $rate / 100;

        $schedule = [];
        $interestAmount = 0;

        switch ($method) {
            case 'flat_rate':
                $interestAmount = $principal * $ratePerPeriod * $period;
                if ($returnSchedule) {
                    $monthlyPrincipal = $principal / $period;
                    $monthlyInterest = $interestAmount / $period;
                    for ($i = 1; $i <= $period; $i++) {
                        $schedule[] = [
                            'principal' => round($monthlyPrincipal, 2),
                            'interest' => round($monthlyInterest, 2),
                            'total' => round($monthlyPrincipal + $monthlyInterest, 2),
                        ];
                    }
                }
                break;

            case 'reducing_balance_with_equal_installment':
                $r = $ratePerPeriod;
                $n = $period;
                $P = $principal;

                $emi = ($P * $r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
                $totalPayable = $emi * $n;
                $interestAmount = $totalPayable - $P;

                if ($returnSchedule) {
                    $balance = $P;
                    for ($i = 1; $i <= $n; $i++) {
                        $interest = $balance * $r;
                        $principalPart = $emi - $interest;
                        $balance -= $principalPart;

                        $schedule[] = [
                            'principal' => round($principalPart, 2),
                            'interest' => round($interest, 2),
                            'total' => round($emi, 2),
                        ];
                    }
                }
                break;

            case 'reducing_balance_with_equal_principal':
                $monthlyPrincipal = $principal / $period;
                $balance = $principal;
                $totalInterest = 0;

                if ($returnSchedule) {
                    for ($i = 1; $i <= $period; $i++) {
                        $interest = $balance * $ratePerPeriod;
                        $totalInterest += $interest;
                        $schedule[] = [
                            'principal' => round($monthlyPrincipal, 2),
                            'interest' => round($interest, 2),
                            'total' => round($monthlyPrincipal + $interest, 2),
                        ];
                        $balance -= $monthlyPrincipal;
                    }
                } else {
                    for ($i = 1; $i <= $period; $i++) {
                        $interest = $balance * $ratePerPeriod;
                        $totalInterest += $interest;
                        $balance -= $monthlyPrincipal;
                    }
                }

                $interestAmount = $totalInterest;
                break;

            default:
                $interestAmount = $principal * $ratePerPeriod * $period;
                break;
        }

        return $returnSchedule ? $schedule : round($interestAmount, 2);
    }



    public function getRepaymentDates()
    {
        $cycle = $this->interest_cycle; // e.g., monthly, weekly
        $period = $this->period;
        $disbursedOn = Carbon::parse($this->disbursed_on);

        // 1. Get first repayment date
        switch ($cycle) {
            case 'Daily':
                $first = $disbursedOn->copy()->addDay();
                $last = $first->copy()->addDays($period - 1);
                break;

            case 'Weekly':
                $first = $disbursedOn->copy()->addWeek();
                $last = $first->copy()->addWeeks($period - 1);
                break;

            case 'Monthly':
                $first = $disbursedOn->copy()->addMonth();
                $last = $first->copy()->addMonths($period - 1);
                break;

            case 'Quarterly':
                $first = $disbursedOn->copy()->addMonths(3);
                $last = $first->copy()->addMonths(3 * ($period - 1));
                break;

            case 'Semi Annually':
                $first = $disbursedOn->copy()->addMonths(6);
                $last = $first->copy()->addMonths(6 * ($period - 1));
                break;

            case 'Annually':
                $first = $disbursedOn->copy()->addYear();
                $last = $first->copy()->addYears($period - 1);
                break;

            default:
                // fallback: monthly
                $first = $disbursedOn->copy()->addMonth();
                $last = $first->copy()->addMonths($period - 1);
        }

        return [
            'first_repayment_date' => $first->toDateString(),
            'last_repayment_date' => $last->toDateString(),
        ];
    }

    public function generateRepaymentSchedule(float $rate)
    {
        $product = $this->product;
        if (!$product) return;

        $principal = $this->amount;
        $interestAmount = $this->interest_amount;
        $period = $this->period;
        $method = strtolower($product->interest_method ?? 'flat_rate');
        $startDate = Carbon::parse($this->first_repayment_date);
        $gracePeriod = $product->grace_period ?? 0;

        $fee = $product->fee;
        $penalty = $product->penalty;

        $isReducing = in_array($method, [
            'reducing_balance_with_equal_installment',
            'reducing_balance_with_equal_principal'
        ]);

        $schedule = $isReducing
            ? $this->calculateInterestAmount($rate, true)
            : array_fill(0, $period, [
                'principal' => round($principal / $period, 2),
                'interest' => round($interestAmount / $period, 2)
            ]);

        foreach ($schedule as $i => $row) {
            $dueDate = $startDate->copy()->addMonths($i);
            $endDate = $dueDate->copy()->addDays(5);
            $endGraceDate = $dueDate->copy()->addDays($gracePeriod);

            // === Fees ===
            $loanFee = 0;
            if ($fee) {
                $feeAmount = $fee->amount;
                $feeType = $fee->fee_type;
                $criteria = $fee->deduction_criteria;

                $applyFee = match ($criteria) {
                    'charge_same_fee_to_all_repayments',
                    'distribute_fee_evenly_to_all_repayments' => true,
                    'charge_fee_on_first_repayment' => $i === 0,
                    'charge_fee_on_last_repayment' => $i === ($period - 1),
                    default => false
                };

                if ($applyFee) {
                    $divideAcross = in_array($criteria, [
                        'charge_same_fee_to_all_repayments',
                        'distribute_fee_evenly_to_all_repayments'
                    ]);

                    $calculated = $feeType === 'percentage'
                        ? ($principal * $feeAmount / 100)
                        : $feeAmount;

                    $loanFee = round($calculated / ($divideAcross ? $period : 1), 2);
                }
            }

            // === Penalty ===
            $penaltyAmount = 0;
            if ($penalty && Carbon::now()->gt($dueDate)) {
                $type = $penalty->penalty_type;
                $criteria = $penalty->deduction_type;

                $base = match ($criteria) {
                    'over_due_principal_amount' => $row['principal'],
                    'over_due_interest_amount' => $row['interest'],
                    'over_due_principal_and_interest' => $row['principal'] + $row['interest'],
                    'total_principal_amount_released' => $principal,
                    default => $principal
                };

                $penaltyAmount = $type === 'percentage'
                    ? round($base * $penalty->amount / 100, 2)
                    : round($penalty->amount, 2);
            }

            LoanSchedule::create([
                'loan_id'        => $this->id,
                'customer_id'    => $this->customer_id,
                'due_date'       => $dueDate,
                'end_date'       => $endDate,
                'end_grace_date' => $endGraceDate,
                'principal'      => $row['principal'],
                'interest'       => $row['interest'],
                'fee_amount'     => $loanFee,
                'penalty_amount' => $penaltyAmount,
            ]);
        }
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
