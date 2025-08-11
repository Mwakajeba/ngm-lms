@extends('layouts.main')

@section('title', 'Loan Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-wallet'],
            ['label' => 'Loan Details', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold text-dark mb-0">Loan Details for {{ $loan->customer->name }}</h4>
            <div class="d-flex gap-2">
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    @php
                        $status = strtolower($loan->status);
                        $badgeClass = match($status) {
                            'pending' => 'bg-secondary',
                            'checked' => 'bg-info',
                            'approved' => 'bg-success',
                            'active' => 'bg-primary',
                            'disbursed' => 'bg-primary',
                            'completed' => 'bg-success',
                            'defaulted' => 'bg-danger',
                            'rejected' => 'bg-danger',
                            'cancelled' => 'bg-dark',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} fs-6">{{ ucfirst($loan->status) }}</span>
                </div>
                <div class="text-end">
                    @php
                        $totalPaid = $loan->repayments?->sum(function($r) { return ($r->principal + $r->interest); }) ?? 0;
                        $progress = $loan->amount_total > 0 ? round(($totalPaid / $loan->amount_total) * 100) : 0;
                        $progressBarClass = match(true) {
                            $progress === 100 => 'bg-success',
                            $progress >= 75 => 'bg-primary',
                            $progress >= 50 => 'bg-info',
                            $progress >= 25 => 'bg-warning',
                            default => 'bg-danger',
                        };
                    @endphp
                    <p class="mb-1 fw-bold text-dark">
                        {{ $progress }}% Complete
                        @if($progress === 100)
                            <span class="badge bg-success ms-2">Fully Paid</span>
                        @elseif($progress === 0)
                            <span class="badge bg-danger ms-2">No Repayments</span>
                        @else
                            <span class="badge bg-warning text-dark ms-2">Partially Paid</span>
                        @endif
                    </p>
                    <div class="progress" style="width: 250px; height: 10px;">
                        <div class="progress-bar {{ $progressBarClass }}" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-style-2 mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active d-flex align-items-center" data-bs-toggle="tab" href="#loan_detail" role="tab">
                    <i class="bx bx-info-circle me-2 font-18"></i>Details
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#schedule" role="tab">
                    <i class="bx bx-calendar me-2 font-18"></i>Schedule
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#guarantors" role="tab">
                    <i class="bx bx-group me-2 font-18"></i>Guarantors
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#documents" role="tab">
                    <i class="bx bx-file me-2 font-18"></i>Documents
                </a>
            </li>

            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#collaterals" role="tab">
                    <i class="bx bx-shield me-2 font-18"></i>Collaterals
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#repayments" role="tab">
                    <i class="bx bx-credit-card me-2 font-18"></i>Repayments
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#approval_history" role="tab">
                    <i class="bx bx-history me-2 font-18"></i>Approval History
                </a>
            </li>
        </ul>

        <div class="tab-content py-3">
            <div class="tab-pane fade show active" id="loan_detail" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary border-0 py-3">
                        <h6 class="mb-0 text-dark fw-bold"><i class="bx bx-info-circle me-2"></i> LOAN INFORMATION</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach([
                            ['label' => 'Customer Name', 'value' => $loan->customer->name, 'icon' => 'bx bx-user'],
                            ['label' => 'Product', 'value' => $loan->product->name, 'icon' => 'bx bx-package'],
                            ['label' => 'Branch', 'value' => $loan->branch->name ?? 'N/A', 'icon' => 'bx bx-building'],
                            ['label' => 'Group', 'value' => $loan->group->name ?? 'N/A', 'icon' => 'bx bx-group'],
                            ['label' => 'Bank Account', 'value' => $loan->bankAccount->name ?? 'N/A', 'icon' => 'bx bx-bank'],
                            ['label' => 'Sector', 'value' => $loan->sector, 'icon' => 'bx bx-tag']
                            ] as $item)
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-light rounded-3 d-flex align-items-center">
                                    <i class="{{ $item['icon'] }} me-3 fs-3 text-primary"></i>
                                    <div>
                                        <p class="text-muted text-uppercase fw-bold mb-0" style="font-size: 0.8rem;">{{ $item['label'] }}</p>
                                        <p class="fw-bold mb-0 text-dark">{{ $item['value'] }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            <div class="col-12">
                                <hr class="my-4">
                            </div>

                            @foreach([
                            ['label' => 'Amount', 'value' => 'TZS ' . number_format($loan->amount, 2), 'icon' => 'bx bx-money'],
                            ['label' => 'Interest Amount', 'value' => 'TZS ' . number_format($loan->interest_amount, 2), 'icon' => 'bx bx-trending-up'],
                            ['label' => 'Total Repayable', 'value' => 'TZS ' . number_format($loan->amount_total, 2), 'icon' => 'bx bx-calculator'],
                            ['label' => 'Period', 'value' => $loan->period . ' months', 'icon' => 'bx bx-time'],
                            ['label' => 'Interest Method', 'value' => $loan->product->interest_method, 'icon' => 'bx bx-bar-chart-alt-2'],
                            ['label' => 'Interest Rate', 'value' => ($loan->interest ?? 'N/A') . '%', 'icon' => 'bx bx-bar-chart-alt-2'],
                            ['label' => 'Repayment Installment', 'value' => 'TZS ' . number_format($loan->amount_total / $loan->period, 2), 'icon' => 'bx bx-credit-card'],
                            ['label' => 'Total Repayments', 'value' => 'TZS ' . number_format($loan->repayments?->sum(function($r) { return ($r->principal + $r->interest); }) ?? 0, 2), 'icon' => 'bx bx-transfer'],
                            ['label' => 'Balance', 'value' => 'TZS ' . number_format($loan->amount_total - ($loan->repayments?->sum(function($r) { return ($r->principal + $r->interest); }) ?? 0), 2), 'icon' => 'bx bx-calculator']
                            ] as $item)
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="{{ $item['icon'] }} me-3 fs-3 text-primary"></i>
                                        <p class="text-muted text-uppercase fw-bold mb-0" style="font-size: 0.8rem;">{{ $item['label'] }}</p>
                                    </div>
                                    <p class="fw-bold mb-0 text-dark">{{ $item['value'] }}</p>
                                </div>
                            </div>
                            @endforeach

                            <div class="col-12">
                                <hr class="my-4">
                            </div>

                            @foreach([
                            ['label' => 'Disbursed On', 'value' => \Carbon\Carbon::parse($loan->disbursed_on)->format('M d, Y'), 'icon' => 'bx bx-calendar-check'],
                            ['label' => 'First Repayment', 'value' => \Carbon\Carbon::parse($loan->first_repayment_date)->format('M d, Y'), 'icon' => 'bx bx-calendar-event'],
                            ['label' => 'Last Repayment', 'value' => \Carbon\Carbon::parse($loan->last_repayment_date)->format('M d, Y'), 'icon' => 'bx bx-calendar-minus'],
                            ['label' => 'Applied On', 'value' => \Carbon\Carbon::parse($loan->date_applied)->format('M d, Y'), 'icon' => 'bx bx-calendar-plus']
                            ] as $item)
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="p-3 bg-light rounded-3 d-flex align-items-center">
                                    <i class="{{ $item['icon'] }} me-3 fs-3 text-primary"></i>
                                    <div>
                                        <p class="text-muted text-uppercase fw-bold mb-0" style="font-size: 0.8rem;">{{ $item['label'] }}</p>
                                        <p class="fw-bold mb-0 text-dark">{{ $item['value'] }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Loan Approval Actions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-cog me-2"></i>LOAN APPROVAL ACTIONS</h6>
                    </div>
                    <div class="card-body">
                        @php
                        $approvalRoles = $loan->getApprovalRoles();
                        $nextLevel = $loan->getNextApprovalLevel();
                        $nextAction = $loan->getNextApprovalAction();
                        $nextRoleName = $nextLevel ? $loan->getApprovalLevelName($nextLevel) : null;
                        @endphp

                        @if($nextLevel && $nextAction)
                        <div class="row g-3">
                            @if(auth()->user() && $loan->canBeApprovedByUser(auth()->user()) && !$loan->hasUserApproved(auth()->user()))
                            <div class="col-md-6 col-lg-4">
                                @can('approve loan')
                                <button type="button" class="btn btn-primary w-100 d-flex align-items-center justify-content-center" onclick="approveLoan('{{ Hashids::encode($loan->id) }}')">
                                    <i class="bx bx-check-circle me-2"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">{{ ucfirst($nextAction) }} Loan</div>
                                        <small class="d-block">{{ $nextRoleName }} (Level {{ $nextLevel }})</small>
                                    </div>
                                </button>
                                @endcan
                            </div>
                            @endif

                            @if($loan->canBeRejected() && auth()->user() && $loan->canBeApprovedByUser(auth()->user()) && !$loan->hasUserApproved(auth()->user()))
                            <div class="col-md-6 col-lg-4">
                                @can('reject loan')
                                <button type="button" class="btn btn-danger w-100 d-flex align-items-center justify-content-center" onclick="rejectLoan('{{ Hashids::encode($loan->id) }}')">
                                    <i class="bx bx-x-circle me-2"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Reject Loan</div>
                                        <small class="d-block">Decline Application</small>
                                    </div>
                                </button>
                                @endcan
                            </div>
                            @endif
                        </div>

                        @if(!auth()->user() || !$loan->canBeApprovedByUser(auth()->user()))
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            @if(!auth()->user())
                            Please log in to perform approval actions.
                            @elseif(!$loan->canBeApprovedByUser(auth()->user()))
                            You don't have permission to approve this loan. Required role: {{ $nextRoleName }}
                            @endif
                        </div>
                        @endif

                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Approval Flow:</strong>
                                @foreach($approvalRoles as $index => $roleId)
                                @php
                                $roleName = $loan->getApprovalLevelName($index + 1);
                                $isCurrent = ($index + 1) === $nextLevel;
                                $isCompleted = ($index + 1) < $nextLevel;
                                    @endphp
                                    <span class="badge {{ $isCurrent ? 'bg-primary' : ($isCompleted ? 'bg-success' : 'bg-secondary') }} me-1">
                                    {{ $roleName }}
                                    </span>
                                    @if($index < count($approvalRoles) - 1)
                                        <i class="bx bx-chevron-right text-muted"></i>
                                        @endif
                                        @endforeach
                            </small>
                        </div>
                        @elseif($loan->status === 'active')
                        <div class="row g-3">
                            @can('default loan')
                            <div class="col-md-6 col-lg-4">
                                <button type="button" class="btn btn-dark w-100 d-flex align-items-center justify-content-center" onclick="defaultLoan('{{ Hashids::encode($loan->id) }}')">
                                    <i class="bx bx-error-circle me-2"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Mark as Defaulted</div>
                                        <small class="d-block">Default Loan</small>
                                    </div>
                                </button>
                            </div>
                            @endcan
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bx bx-info-circle fs-1 text-muted mb-3"></i>
                            <h6 class="text-muted">No Actions Available</h6>
                            <p class="text-muted">
                                @if(empty($approvalRoles))
                                This loan product does not require approval levels.
                                @else
                                This loan status does not require any approval actions.
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="schedule" role="tabpanel">
                @if($loan->schedule->count())
                <div class="card radius-10">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-history me-2"></i>LOAN SCHEDULE LIST</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive w-100" style="overflow-x: auto;">
                            <table class="table table-bordered nowrap w-100 table-striped">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Due Date</th>
                                        <th>Principal</th>
                                        <th>Interest</th>
                                        <th>Penalty Amount</th>
                                        <th>Fee Amount</th>
                                        <th class="text-end pe-4">Total Due</th>
                                        <th class="text-end pe-4">Paid Amount</th>
                                        <th class="text-end pe-4">Remaining</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->schedule->sortBy('due_date') as $index => $item)
                                    @php
                                        $totalDue = $item->total_due;
                                        $paidAmount = $item->paid_amount;
                                        $remainingAmount = $item->remaining_amount;
                                        $isFullyPaid = $item->is_fully_paid;
                                        $paymentPercentage = $item->payment_percentage;
                                    @endphp
                                    <tr class="{{ $isFullyPaid ? 'table-success' : ($paidAmount > 0 ? 'table-warning' : '') }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="ps-4">{{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}</td>
                                        <td>{{ number_format($item->principal, 2) }}</td>
                                        <td>{{ number_format($item->interest, 2) }}</td>
                                        <td>{{ number_format($item->penalty_amount, 2) }}</td>
                                        <td>{{ number_format($item->fee_amount, 2) }}</td>
                                        <td class="text-end pe-4 fw-bold">{{ number_format($totalDue, 2) }}</td>
                                        <td class="text-end pe-4 text-success">{{ number_format($paidAmount, 2) }}</td>
                                        <td class="text-end pe-4 text-danger">{{ number_format($remainingAmount, 2) }}</td>
                                        <td class="text-center">
                                            @if($isFullyPaid)
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($paidAmount > 0)
                                                <span class="badge bg-warning text-dark">{{ $paymentPercentage }}%</span>
                                            @else
                                                <span class="badge bg-danger">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isFullyPaid)
                                                <button type="button" class="btn btn-sm btn-success" disabled>
                                                    <i class="bx bx-check-circle me-1"></i>Paid
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-primary" onclick="repayScheduleItem('{{ $item->id }}', '{{ number_format($remainingAmount, 2) }}', '{{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}', '{{ number_format($item->principal, 2) }}', '{{ number_format($item->interest, 2) }}', '{{ number_format($item->penalty_amount, 2) }}', '{{ number_format($item->fee_amount, 2) }}')">
                                                    <i class="bx bx-credit-card me-1"></i>Repay
                                                </button>
                                            @endif
                                            @if($item->penalty_amount > 0 && !$isFullyPaid)
                                            <button type="button" class="btn btn-sm btn-warning ms-1" onclick="removePenalty('{{ $item->id }}', '{{ number_format($item->penalty_amount, 2) }}')">
                                                <i class="bx bx-x-circle me-1"></i>Remove Penalty
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="card card-body text-center p-5">
                    <h4 class="text-muted">No repayment schedule available.</h4>
                    <p class="text-secondary">A schedule will be generated once the loan is approved.</p>
                </div>
                @endif
            </div>


            <div class="tab-pane fade" id="guarantors" role="tabpanel">

                @can('add guarantor')
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Guarantors</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addGuarantorModal">
                        <i class="bx bx-user-plus me-2 font-18"></i>Add Guarantor
                    </button>
                </div>
                @endcan

                @if($loan->guarantors && $loan->guarantors->count())
                <div class="card radius-10">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-history me-2"></i>GUARANTOR LIST</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->guarantors as $index => $guarantor)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $guarantor->name }}</td>
                                        <td>{{ $guarantor->phone1 }}</td>
                                        @can('remove guarantor')
                                        <td class="text-end pe-4">
                                            <form action="{{ route('loans.removeGuarantor', [$loan->id, $guarantor->id]) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-name="{{$guarantor->name}}">Remove</button>
                                            </form>
                                        </td>
                                        @endcan
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="card card-body text-center p-5">
                    <h4 class="text-muted">No guarantors assigned to this loan.</h4>
                    <p class="text-secondary">Click the button above to add a guarantor.</p>
                </div>
                @endif

            </div>

            <div class="tab-pane fade" id="documents" role="tabpanel">
                @can('manage loan documents')
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Documents</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="bx bx-cloud-upload me-2 font-18"></i>Upload Document
                    </button>
                </div>
                @endcan

                @if($loan->loanFiles->count())
                <div class="card radius-10">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-history me-2"></i>DOCUMENT LIST</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Document Name</th>
                                        <th class="text-end pe-4">>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->loanFiles as $index => $doc)
                                    <tr>
                                        <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                        <td>{{ $doc->fileType->name }}</td>
                                        @can('view loan documents')
                                        <td class="text-end pe-4">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary me-2">
                                                View
                                            </a>
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" download class="btn btn-sm btn-primary">
                                                Download
                                            </a>
                                        </td>
                                        @endcan
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="card card-body text-center p-5">
                    <h4 class="text-muted">No documents uploaded yet.</h4>
                    <p class="text-secondary">Click the button above to add the first document.</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="repayments" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Repayments</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addRepaymentModal">
                        <i class="bx bx-plus me-2 font-18"></i>Add Repayment
                    </button>
                </div>

                @if($loan->repayments && $loan->repayments->count())
                <div class="card radius-10">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-history me-2"></i>REPAYMENT LIST</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Payment Date</th>
                                        <th>Due Date</th>
                                        <th>Principal</th>
                                        <th>Interest</th>
                                        <th>Penalty</th>
                                        <th>Fee</th>
                                        <th class="text-end pe-4">Total Paid</th>
                                        <th>Bank Account</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->repayments->sortByDesc('payment_date') as $index => $repayment)
                                    <tr>
                                        <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                        <td>{{ \Carbon\Carbon::parse($repayment->payment_date)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($repayment->due_date)->format('M d, Y') }}</td>
                                        <td class="text-success">{{ number_format($repayment->principal, 2) }}</td>
                                        <td class="text-info">{{ number_format($repayment->interest, 2) }}</td>
                                        <td class="text-danger">{{ number_format($repayment->penalt_amount, 2) }}</td>
                                        <td class="text-warning">{{ number_format($repayment->fee_amount, 2) }}</td>
                                        <td class="text-end pe-4 fw-bold">{{ number_format($repayment->amount_paid, 2) }}</td>
                                        <td>{{ $repayment->bankAccount->name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="printReceipt({{ $repayment->id }})" title="Print Receipt">
                                                    Print
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editRepayment({{ $repayment->id }})" title="Edit Repayment">
                                                    Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRepayment({{ $repayment->id }})" title="Delete Repayment">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="card card-body text-center p-5">
                    <h4 class="text-muted">No repayments recorded yet.</h4>
                    <p class="text-secondary">Click the button above to add the first repayment.</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="collaterals" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Collaterals</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addCollateralModal">
                        <i class="bx bx-plus me-2 font-18"></i>Add Collateral
                    </button>
                </div>

                @if($loan->collaterals && $loan->collaterals->count())
                <div class="card radius-10">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-history me-2"></i>LOAN COLLATERAL LIST</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Value</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->collaterals as $index => $collateral)
                                    <tr>
                                        <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                        <td>{{ ucfirst($collateral->type ?? 'N/A') }}</td>
                                        <td>{{ $collateral->description ?? 'N/A' }}</td>
                                        <td>{{ number_format($collateral->value ?? 0, 2) }}</td>
                                        <td class="text-end pe-4">
                                            <a href="#" class="btn btn-sm btn-outline-secondary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="card card-body text-center p-5">
                    <h4 class="text-muted">No collaterals assigned to this loan.</h4>
                    <p class="text-secondary">Click the button above to add a collateral.</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="approval_history" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-history me-2"></i>APPROVAL HISTORY</h6>
                    </div>
                    <div class="card-body">
                        @if($loan->approvals && $loan->approvals->count())
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Action</th>
                                        <th>Level</th>
                                        <th>User</th>
                                        <th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->approvals as $history)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($history->approved_at)->format('d-m-Y H:i') }}</td>
                                        <td>{{ ucfirst($history->action ?? 'N/A') }}</td>
                                        <td>
                                            <span class="badge bg-info">Level {{ $history->approval_level }}</span>
                                        </td>
                                        <td>{{ $history->user->name ?? 'System' }}</td>
                                        <td>{{ $history->comments ?? 'No comments' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bx bx-history fs-1 text-muted mb-3"></i>
                            <h6 class="text-muted">No approval history available</h6>
                            <p class="text-muted">Approval history will be recorded as the loan progresses through the approval process.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addGuarantorModal" tabindex="-1" aria-labelledby="addGuarantorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('loans.addGuarantor', $loan->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGuarantorModalLabel">Add Guarantor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                    <div class="mb-3">
                        <label for="guarantor_id" class="form-label">Select Guarantor</label>
                        <select class="form-select" name="guarantor_id" id="guarantor_id" required>
                            <option value="">-- Choose Guarantor --</option>
                            @foreach($guarantorCustomers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone1 }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="relation" class="form-label">Relation to Borrower</label>
                        <input type="text" class="form-control" name="relation" id="relation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Guarantor</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('loan-documents.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="loan_id" value="{{ $loan->id }}">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadDocumentLabel">Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="guarantor_id" class="form-label">File Type</label>
                    <select class="form-select" name="file_type_id" id="file_type_id" required>
                        <option value="">-- Select Document Type --</option>
                        @foreach($filetypes as $file)
                        <option value="{{ $file->id }}">{{ $file->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="docFile" class="form-label">Choose File</label>
                    <input type="file" class="form-control" name="file" id="docFile" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Repayment Modal -->
<div class="modal fade" id="addRepaymentModal" tabindex="-1" aria-labelledby="addRepaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="#" method="POST" class="modal-content" id="repaymentForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addRepaymentModalLabel">Add Repayment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="payment_date" class="form-label">Payment Date</label>
                    <input type="date" class="form-control" name="payment_date" id="payment_date" required>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" step="0.01" class="form-control" name="amount" id="amount" required>
                </div>
                <div class="mb-3">
                    <label for="payment_type" class="form-label">Payment Type</label>
                    <select class="form-select" name="payment_type" id="payment_type" required>
                        <option value="regular">Regular Payment</option>
                        <option value="early">Early Payment</option>
                        <option value="late">Late Payment</option>
                        <option value="partial">Partial Payment</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="comments" class="form-label">Comments</label>
                    <textarea class="form-control" name="comments" id="comments" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Repayment</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Collateral Modal -->
<div class="modal fade" id="addCollateralModal" tabindex="-1" aria-labelledby="addCollateralModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="#" method="POST" class="modal-content" id="collateralForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addCollateralModalLabel">Add Collateral</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="collateral_type" class="form-label">Collateral Type</label>
                    <select class="form-select" name="type" id="collateral_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="property">Property</option>
                        <option value="vehicle">Vehicle</option>
                        <option value="equipment">Equipment</option>
                        <option value="cash">Cash</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="value" class="form-label">Value</label>
                    <input type="number" step="0.01" class="form-control" name="value" id="value" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Collateral</button>
            </div>
        </form>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="approvalMessage"></p>
                <div class="mb-3">
                    <label for="comments" class="form-label">Comments (Optional)</label>
                    <textarea class="form-control" name="comments" id="comments" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="approvalForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Repay Schedule Modal -->
<div class="modal fade" id="repayScheduleModal" tabindex="-1" aria-labelledby="repayScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('repayments.store') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="loan_id" value="{{ $loan->id }}">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="repayScheduleModalLabel">
                    <i class="bx bx-credit-card me-2"></i>Repay Schedule Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="schedule_id" id="schedule_id">
                
                <!-- Schedule Details Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Schedule Details</h6>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Due Date</label>
                            <p id="modal_due_date" class="fw-bold text-dark mb-0"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Total Installment</label>
                            <p id="modal_total_installment" class="fw-bold text-success mb-0"></p>
                        </div>
                    </div>
                </div>

                <!-- Breakdown Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="bx bx-calculator me-2"></i>Amount Breakdown</h6>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Principal</label>
                            <p id="modal_principal" class="fw-bold text-dark mb-0"></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Interest</label>
                            <p id="modal_interest" class="fw-bold text-dark mb-0"></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Penalty</label>
                            <p id="modal_penalty" class="fw-bold text-danger mb-0"></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Fee</label>
                            <p id="modal_fee" class="fw-bold text-warning mb-0"></p>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Payment Details Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="bx bx-credit-card me-2"></i>Payment Details</h6>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date" id="payment_date" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" class="form-control" name="amount" id="payment_amount" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="bank_account_id" class="form-label">Bank Account</label>
                            <select class="form-select" name="bank_account_id" id="bank_account_id" required>
                                <option value="">-- Select Bank Account --</option>
                                @foreach($bankAccounts ?? [] as $bankAccount)
                                <option value="{{ $bankAccount->id }}">{{ $bankAccount->name }} - {{ $bankAccount->account_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-check me-1"></i>Add Repayment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Repayment Modal -->
<div class="modal fade" id="editRepaymentModal" tabindex="-1" aria-labelledby="editRepaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editRepaymentForm" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="editRepaymentModalLabel">
                    <i class="bx bx-edit me-2"></i>Edit Repayment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_payment_date" class="form-label">Payment Date</label>
                    <input type="date" class="form-control" name="payment_date" id="edit_payment_date" required>
                </div>
                <div class="mb-3">
                    <label for="edit_amount" class="form-label">Amount</label>
                    <input type="number" step="0.01" class="form-control" name="amount" id="edit_amount" required>
                </div>
                <div class="mb-3">
                    <label for="edit_bank_account_id" class="form-label">Bank Account</label>
                    <select class="form-select" name="bank_account_id" id="edit_bank_account_id" required>
                        <option value="">-- Select Bank Account --</option>
                        @foreach($bankAccounts ?? [] as $bankAccount)
                        <option value="{{ $bankAccount->id }}">{{ $bankAccount->name }} - {{ $bankAccount->account_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="submit" class="btn btn-secondary">
                    <i class="bx bx-check me-1"></i>Update Repayment
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Toast notification function
    function showToast(title, message, type = 'info') {
        const toastClass = type === 'success' ? 'bg-success' : 
                          type === 'error' ? 'bg-danger' : 
                          type === 'warning' ? 'bg-warning' : 'bg-info';
        
        const toastHtml = `
            <div class="toast align-items-center text-white ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        // Add toast to container
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Get the last added toast and show it
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }

    $(document).ready(function() {
        $('#loansTableDetail').DataTable({
            responsive: true,
            order: [
                [1, 'asc'] // Sort by due date column (index 1) in ascending order
            ],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search loans..."
            },
            columnDefs: [{
                    targets: -1,
                    responsivePriority: 1,
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [0, 1, 2],
                    responsivePriority: 2
                }
            ]
        });

        // Handle collateral form submission
        $('#collateralForm').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Feature Not Implemented',
                text: 'Collateral management functionality will be implemented soon.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        });

        // Handle repayment schedule form submission
        $('#repayScheduleModal form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Processing...');
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Close modal
                    $('#repayScheduleModal').modal('hide');
                    
                    // Show success toast
                    showToast('Success!', 'Repayment recorded successfully!', 'success');
                    
                    // Reload the page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while processing the repayment.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        // Try to extract error message from response
                        const match = xhr.responseText.match(/<title[^>]*>([^<]+)<\/title>/);
                        if (match) {
                            errorMessage = match[1];
                        }
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });

    function repayScheduleItem(scheduleId, amount, dueDate, principal, interest, penalty, fee) {
        // Set modal values
        document.getElementById('schedule_id').value = scheduleId;
        document.getElementById('modal_due_date').textContent = dueDate;
        document.getElementById('modal_total_installment').textContent = 'TZS ' + amount;
        document.getElementById('modal_principal').textContent = 'TZS ' + principal;
        document.getElementById('modal_interest').textContent = 'TZS ' + interest;
        document.getElementById('modal_penalty').textContent = 'TZS ' + penalty;
        document.getElementById('modal_fee').textContent = 'TZS ' + fee;
        document.getElementById('payment_amount').value = amount.replace(/[^\d.]/g, ''); // Remove TZS and commas
        document.getElementById('payment_date').value = new Date().toISOString().split('T')[0]; // Set today's date
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('repayScheduleModal'));
        modal.show();
    }

    function removePenalty(scheduleId, penaltyAmount) {
        Swal.fire({
            title: 'Remove Penalty',
            html: `
                <div class="text-start">
                    <p><strong>Penalty Amount:</strong> TZS ${penaltyAmount}</p>
                    <p class="text-muted">This will remove the penalty from this schedule item.</p>
                    <div class="mb-3">
                        <label for="penalty_reason" class="form-label">Reason for Removal (Optional)</label>
                        <textarea class="form-control" id="penalty_reason" rows="3" placeholder="Enter reason for penalty removal..."></textarea>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Remove Penalty',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            preConfirm: () => {
                return {
                    reason: document.getElementById('penalty_reason').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request to remove penalty
                $.ajax({
                    url: `/repayments/remove-penalty/${scheduleId}`,
                    method: 'POST',
                    data: {
                        reason: result.value.reason,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast('Success!', 'Penalty removed successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to remove penalty.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }

    function disburseLoan(loanId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');

        message.textContent = 'Are you sure you want to disburse this loan? This will mark the loan as disbursed and activate the repayment schedule.';
        form.action = `/loans/${loanId}/disburse`;

        modal.show();
    }

    function approveLoan(loanId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');

        message.textContent = 'Are you sure you want to approve this loan? This will change the loan status to approved.';
        form.action = `/loans/${loanId}/approve`;

        modal.show();
    }

    function checkLoan(loanId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');

        message.textContent = 'Are you sure you want to check this loan? This will mark the loan as checked for first level approval.';
        form.action = `/loans/${loanId}/check`;

        modal.show();
    }

    function authorizeLoan(loanId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');

        message.textContent = 'Are you sure you want to authorize this loan? This will mark the loan as authorized for final approval.';
        form.action = `/loans/${loanId}/authorize`;

        modal.show();
    }

    function rejectLoan(loanId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');

        message.textContent = 'Are you sure you want to reject this loan? This action cannot be undone.';
        form.action = `/loans/${loanId}/reject`;

        modal.show();
    }

    function approveApplication(loanId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');

        message.textContent = 'Are you sure you want to approve this loan application? This will convert it to an active loan.';
        form.action = `/loans/application/${loanId}/approve`;

        modal.show();
    }

    function defaultLoan(loanId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');

        message.textContent = 'Are you sure you want to mark this loan as defaulted? This will change the loan status to defaulted.';
        form.action = `/loans/${loanId}/default`;

        modal.show();
    }

    function deleteLoan(loanId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/loans/${loanId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Repayment Management Functions
    function editRepayment(repaymentId) {
        // Fetch repayment data
        $.ajax({
            url: `/repayments/${repaymentId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const repayment = response.repayment;
                    
                    // Set form action
                    $('#editRepaymentForm').attr('action', `/repayments/${repaymentId}`);
                    
                    // Populate form fields
                    $('#edit_payment_date').val(repayment.payment_date);
                    $('#edit_amount').val(repayment.cash_deposit);
                    $('#edit_bank_account_id').val(repayment.bank_account_id);
                    
                    // Show modal
                    $('#editRepaymentModal').modal('show');
                }
            },
            error: function(xhr) {
                showToast('Error!', 'Failed to load repayment data', 'error');
            }
        });
    }

    function deleteRepayment(repaymentId) {
        Swal.fire({
            title: 'Delete Repayment?',
            text: "This will also delete associated receipts and GL transactions. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/repayments/${repaymentId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('Success!', response.message, 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to delete repayment.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showToast('Error!', errorMessage, 'error');
                    }
                });
            }
        });
    }

    function printReceipt(repaymentId) {
        // Show loading
        Swal.fire({
            title: 'Generating Receipt...',
            text: 'Please wait while we prepare your receipt for printing.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `/repayments/${repaymentId}/print`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    Swal.close();
                    
                    // Generate thermal printer receipt
                    const receiptData = response.receipt_data;
                    printThermalReceipt(receiptData);
                    
                    showToast('Success!', 'Receipt generated successfully!', 'success');
                } else {
                    Swal.close();
                    showToast('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMessage = 'Failed to generate receipt.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showToast('Error!', errorMessage, 'error');
            }
        });
    }

    function printThermalReceipt(receiptData) {
        // Create a new window for thermal printer (narrow width)
        const printWindow = window.open('', '_blank', 'width=320,height=600');
        
        // Set the document title to customer name for printing
        const customerName = receiptData.customer_name.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_');
        const fileName = `Receipt_${customerName}_${receiptData.date}`;
        
        const receiptHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>${fileName}</title>
                <style>
                    @page {
                        size: 80mm 200mm;
                        margin: 0;
                        padding: 0;
                    }
                    
                    @media print {
                        body { 
                            font-family: 'Courier New', monospace; 
                            font-size: 10px; 
                            margin: 0; 
                            padding: 5px;
                            width: 280px;
                            max-width: 280px;
                            min-width: 280px;
                            page-break-after: avoid;
                            page-break-before: avoid;
                        }
                        .header { text-align: center; margin-bottom: 8px; }
                        .title { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
                        .subtitle { font-size: 10px; margin-bottom: 8px; }
                        .divider { border-top: 1px dashed #000; margin: 8px 0; }
                        .row { display: flex; justify-content: space-between; margin: 2px 0; }
                        .label { font-weight: bold; }
                        .value { text-align: right; }
                        .total { font-weight: bold; font-size: 12px; }
                        .footer { text-align: center; margin-top: 15px; font-size: 8px; }
                        .center { text-align: center; }
                        .bold { font-weight: bold; }
                        
                        /* Force thermal printer format */
                        html, body {
                            width: 280px !important;
                            max-width: 280px !important;
                            min-width: 280px !important;
                        }
                    }
                    
                    body { 
                        font-family: 'Courier New', monospace; 
                        font-size: 10px; 
                        margin: 0; 
                        padding: 5px;
                        width: 280px;
                        max-width: 280px;
                        min-width: 280px;
                    }
                    .header { text-align: center; margin-bottom: 8px; }
                    .title { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
                    .subtitle { font-size: 10px; margin-bottom: 8px; }
                    .divider { border-top: 1px dashed #000; margin: 8px 0; }
                    .row { display: flex; justify-content: space-between; margin: 2px 0; }
                    .label { font-weight: bold; }
                    .value { text-align: right; }
                    .total { font-weight: bold; font-size: 12px; }
                    .footer { text-align: center; margin-top: 15px; font-size: 8px; }
                    .center { text-align: center; }
                    .bold { font-weight: bold; }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="title">SMARTFINANCE</div>
                    <div class="subtitle">Loan Repayment Receipt</div>
                </div>
                
                <div class="divider"></div>
                
                <div class="row">
                    <span class="label">Customer:</span>
                    <span class="value">${receiptData.customer_name}</span>
                </div>
                <div class="row">
                    <span class="label">Loan No:</span>
                    <span class="value">${receiptData.loan_number}</span>
                </div>
                
                <div class="divider"></div>
                
                <div class="row">
                    <span class="label">Receipt No:</span>
                    <span class="value">${receiptData.receipt_number}</span>
                </div>
                <div class="row">
                    <span class="label">Date:</span>
                    <span class="value">${receiptData.date}</span>
                </div>
                <div class="row">
                    <span class="label">Time:</span>
                    <span class="value">${new Date().toLocaleTimeString()}</span>
                </div>
                <div class="row">
                    <span class="label">Bank Account:</span>
                    <span class="value">${receiptData.bank_account}</span>
                </div>
                
                <div class="divider"></div>
                
                <div class="center bold">PAYMENT BREAKDOWN</div>
                
                <div class="row">
                    <span class="label">Principal:</span>
                    <span class="value">TZS ${receiptData.payment_breakdown.principal.toLocaleString()}</span>
                </div>
                <div class="row">
                    <span class="label">Interest:</span>
                    <span class="value">TZS ${receiptData.payment_breakdown.interest.toLocaleString()}</span>
                </div>
                <div class="row">
                    <span class="label">Penalty:</span>
                    <span class="value">TZS ${receiptData.payment_breakdown.penalty.toLocaleString()}</span>
                </div>
                <div class="row">
                    <span class="label">Fee:</span>
                    <span class="value">TZS ${receiptData.payment_breakdown.fee.toLocaleString()}</span>
                </div>
                
                <div class="divider"></div>
                
                <div class="row total">
                    <span class="label">TOTAL PAID:</span>
                    <span class="value">TZS ${receiptData.amount_paid.toLocaleString()}</span>
                </div>
                
                <div class="divider"></div>
                
                <div class="row">
                    <span class="label">Received By:</span>
                    <span class="value">${receiptData.received_by}</span>
                </div>
                <div class="row">
                    <span class="label">Branch:</span>
                    <span class="value">${receiptData.branch}</span>
                </div>
                
                <div class="divider"></div>
                
                <div class="footer">
                    <div class="bold">Thank you for your payment!</div>
                    <div>Keep this receipt for your records</div>
                    <div style="margin-top: 5px;">--- End of Receipt ---</div>
                </div>
            </body>
            </html>
        `;
        
        printWindow.document.write(receiptHtml);
        printWindow.document.close();
        
        // Print after a short delay
        setTimeout(() => {
            // Set print options for thermal printer
            const printOptions = {
                silent: false,
                printBackground: false,
                color: false,
                margin: {
                    marginType: 'none',
                    top: 0,
                    bottom: 0,
                    left: 0,
                    right: 0
                },
                landscape: false,
                pagesPerSheet: 1,
                collate: false,
                copies: 1,
                header: '',
                footer: ''
            };
            
            // Try to use print options if available (Electron/Chrome)
            if (printWindow.print) {
                printWindow.print();
            } else {
                // Fallback for regular browsers
                printWindow.document.execCommand('print', false, null);
            }
            
            printWindow.close();
        }, 500);
    }

    // Handle edit repayment form submission
    $('#editRepaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Updating...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'PUT',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Close modal
                $('#editRepaymentModal').modal('hide');
                
                if (response.success) {
                    showToast('Success!', response.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to update repayment.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showToast('Error!', errorMessage, 'error');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
</script>
@endpush