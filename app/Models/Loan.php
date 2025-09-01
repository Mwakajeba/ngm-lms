<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Optional if you want soft deletes

class Loan extends Model
{
    // Uncomment if using soft deletes
    // use SoftDeletes;
    use HasFactory, LogsActivity;

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

    public function collaterals()
    {
        return $this->hasMany(\App\Models\LoanCollateral::class, 'loan_id');
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

    public function loanOfficer()
    {
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
        if (!$product)
            return;

        $principal = $this->amount;
        $interestAmount = $this->interest_amount;
        $period = $this->period;
        $method = strtolower($product->interest_method ?? 'flat_rate');
        $startDate = Carbon::parse($this->first_repayment_date);
        $gracePeriod = $product->grace_period ?? 0;

        $fee = $product->schedule_fee;
        \Log::info('[LoanSchedule] Fee: ' . $fee);
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
                $feeAmount = (float) $fee->amount;
                $feeType = $fee->fee_type;
                $criteria = $fee->deduction_criteria;
                $includeInSchedule = $fee->include_in_schedule;
                $status = $fee->status;

                \Log::info('[LoanSchedule] Repayment #' . $i . ' Fee ID: ' . $fee->id . ' include_in_schedule: ' . ($includeInSchedule ? 'true' : 'false') . ', status: ' . $status);

                if ($includeInSchedule && $status === 'active') {
                    // Total fee basis (for distribution or per-installment use)
                    $totalFee = $feeType === 'percentage'
                        ? ((float) $principal * (float) $feeAmount / 100)
                        : (float) $feeAmount;
                    $totalFeeFloat = (float) $totalFee;

                    switch ($criteria) {
                        case 'distribute_fee_evenly_to_all_repayments':
                            // Spread the total fee evenly across all installments
                            $loanFee = round($totalFeeFloat / max(1, $period), 2);
                            break;

                        case 'charge_same_fee_to_all_repayments':
                            // Charge the same fee amount on every installment (no division)
                            $loanFee = round($totalFeeFloat, 2);
                            break;

                        case 'charge_fee_on_first_repayment':
                            $loanFee = $i === 0 ? round($totalFeeFloat, 2) : 0;
                            break;

                        case 'charge_fee_on_last_repayment':
                            $loanFee = $i === ($period - 1) ? round($totalFeeFloat, 2) : 0;
                            break;

                        case 'do_not_include_in_loan_schedule':
                        case 'charge_fee_on_release_date':
                        default:
                            $loanFee = 0; // Not applied on schedule rows
                            break;
                    }

                    \Log::info('[LoanSchedule] Repayment #' . $i . ' Fee criteria: ' . $criteria . ' Applied amount: ' . $loanFee);
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
                    ? round((float) $base * (float) $penalty->amount / 100, 2)
                    : round((float) $penalty->amount, 2);
            }

            LoanSchedule::create([
                'loan_id' => $this->id,
                'customer_id' => $this->customer_id,
                'due_date' => $dueDate,
                'end_date' => $endDate,
                'end_grace_date' => $endGraceDate,
                'principal' => $row['principal'],
                'interest' => $row['interest'],
                'fee_amount' => $loanFee,
                'penalty_amount' => $penaltyAmount,
            ]);
        }
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'reference')
            ->where('reference_type', 'loan');
    }

    /**
     * Calculate the total amount in arrears (overdue amount)
     */
    public function getArrearsAmountAttribute()
    {
        $today = Carbon::now();
        $totalArrears = 0;

        foreach ($this->schedule as $scheduleItem) {
            $dueDate = Carbon::parse($scheduleItem->due_date);

            // If the due date has passed and there's a remaining amount
            if ($dueDate->lt($today) && $scheduleItem->remaining_amount > 0) {
                $totalArrears += $scheduleItem->remaining_amount;
            }
        }

        return $totalArrears;
    }

    /**
     * Calculate the number of days in arrears (days since first overdue payment)
     */
    public function getDaysInArrearsAttribute()
    {
        $today = Carbon::now();
        $firstOverdueDate = null;

        foreach ($this->schedule->sortBy('due_date') as $scheduleItem) {
            $dueDate = Carbon::parse($scheduleItem->due_date);

            // If the due date has passed and there's a remaining amount
            if ($dueDate->lt($today) && $scheduleItem->remaining_amount > 0) {
                $firstOverdueDate = $dueDate;
                break; // We found the first overdue date
            }
        }

        if ($firstOverdueDate) {
            return round($firstOverdueDate->diffInDays($today));
        }

        return 0; // No arrears
    }

    /**
     * Check if the loan is in arrears
     */
    public function getIsInArrearsAttribute()
    {
        return $this->arrears_amount > 0;
    }

    /**
     * Check if the loan is eligible for top-up based on product settings
     * 
     * @return bool
     */
    public function isEligibleForTopUp(): bool
    {
        $product = $this->product;

        // Step 1: Check if product exists and top-up is allowed
        if (!$product || !$product->top_up_type || !$product->top_up_type_value) {
            info('Top-up eligibility check failed: Product or top-up settings not found', [
                'loan_id' => $this->id,
                'product_id' => $this->product_id
            ]);
            return false;
        }

        // Check if loan is active
        if ($this->status !== self::STATUS_ACTIVE) {
            info('Top-up eligibility check failed: Loan is not active', [
                'loan_id' => $this->id,
                'status' => $this->status
            ]);
            return false;
        }

        // Check if loan has arrears
        if ($this->is_in_arrears) {
            info('Top-up eligibility check failed: Loan has arrears', [
                'loan_id' => $this->id,
                'arrears_amount' => $this->arrears_amount
            ]);
            return false;
        }

        // Check if loan already has top-up children
        if ($this->topUpChildren()->exists()) {
            info('Top-up eligibility check failed: Loan already has top-up children', [
                'loan_id' => $this->id
            ]);
            return false;
        }

        // Check if this loan is itself a top-up loan
        if ($this->top_up_id) {
            info('Top-up eligibility check failed: Loan is itself a top-up loan', [
                'loan_id' => $this->id,
                'top_up_id' => $this->top_up_id
            ]);
            return false;
        }

        // Step 2: Fetch loan schedules
        $schedules = $this->schedule;

        // Step 3: Get top-up type and value
        $type = $product->top_up_type;
        $value = $product->top_up_type_value;

        info('Top-up eligibility check data', [
            'loan_id' => $this->id,
            'type' => $type,
            'value' => $value,
            'schedules_count' => $schedules->count()
        ]);

        switch ($type) {
            case 'number_of_installment':
                // Get paid amount and calculate total amount for required installments
                $paidAmount = $this->getTotalPaidAmount();
                $installmentAmount = $this->getInstallmentAmount();

                if ($installmentAmount <= 0) {
                    info('Top-up eligibility check failed: Invalid installment amount', [
                        'loan_id' => $this->id,
                        'installment_amount' => $installmentAmount
                    ]);
                    return false;
                }

                // Calculate total amount for the required number of installments
                $requiredInstallmentsAmount = $installmentAmount * $value;

                info('Top-up eligibility check - installments amount', [
                    'loan_id' => $this->id,
                    'paid_amount' => $paidAmount,
                    'installment_amount' => $installmentAmount,
                    'required_installments' => $value,
                    'required_installments_amount' => $requiredInstallmentsAmount,
                    'is_eligible' => $paidAmount >= $requiredInstallmentsAmount
                ]);

                return $paidAmount >= $requiredInstallmentsAmount;

            case 'percentage':
                // Calculate percentage of total amount paid
                $totalToPay = $this->getTotalAmountToPay();
                $totalPaid = $this->getTotalPaidAmount();

                if ($totalToPay <= 0) {
                    info('Top-up eligibility check failed: Invalid total amount to pay', [
                        'loan_id' => $this->id,
                        'total_to_pay' => $totalToPay
                    ]);
                    return false;
                }

                $paidPercentage = ($totalPaid / $totalToPay) * 100;

                info('Top-up eligibility check - percentage', [
                    'loan_id' => $this->id,
                    'total_paid' => $totalPaid,
                    'total_to_pay' => $totalToPay,
                    'paid_percentage' => $paidPercentage,
                    'required_percentage' => $value,
                    'is_eligible' => $paidPercentage >= $value
                ]);

                return $paidPercentage >= $value;

            case 'fixed_amount':
                // Check if paid amount has reached the required fixed amount for top-up
                $paidAmount = $this->getTotalPaidAmount();
                $requiredAmount = $value;
                $isEligible = $paidAmount >= $requiredAmount;

                info('Top-up eligibility check - fixed amount', [
                    'loan_id' => $this->id,
                    'paid_amount' => $paidAmount,
                    'required_amount' => $requiredAmount,
                    'is_eligible' => $isEligible
                ]);

                return $isEligible;

            default:
                info('Top-up eligibility check failed: Unknown top-up type', [
                    'loan_id' => $this->id,
                    'type' => $type
                ]);
                return false;
        }
    }



    /**
     * Get the calculated top-up amount for this loan
     * The top-up amount is the remaining balance of the loan
     * 
     * @return float
     */
    public function getCalculatedTopUpAmount(): float
    {
        if (!$this->isEligibleForTopUp()) {
            return 0;
        }

        // Calculate the outstanding balance from schedule and repayments
        $totalOutstanding = 0;

        foreach ($this->schedule as $scheduleItem) {
            $totalOutstanding += $scheduleItem->remaining_amount;
        }

        return max(0, round($totalOutstanding, 2));
    }

    /**
     * Get the total amount paid for this loan
     * 
     * @return float
     */
    public function getTotalPaidAmount(): float
    {
        return $this->repayments->sum(function ($repayment) {
            return $repayment->principal + $repayment->interest + $repayment->fee_amount + $repayment->penalt_amount;
        });
    }

    /**
     * Get the total amount to pay for this loan (from schedule)
     * 
     * @return float
     */
    public function getTotalAmountToPay(): float
    {
        return $this->schedule->sum(function ($scheduleItem) {
            return $scheduleItem->principal + $scheduleItem->interest + $scheduleItem->fee_amount + $scheduleItem->penalty_amount;
        });
    }

    /**
     * Get the installment amount (average amount per installment)
     * 
     * @return float
     */
    public function getInstallmentAmount(): float
    {
        $totalAmount = $this->getTotalAmountToPay();
        $totalInstallments = $this->period;

        if ($totalInstallments <= 0) {
            return 0;
        }

        return round($totalAmount / $totalInstallments, 2);
    }
}
