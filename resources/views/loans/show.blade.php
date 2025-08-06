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
                    <span class="badge bg-primary fs-6">{{ ucfirst($loan->status) }}</span>
                </div>
                <div class="text-end">
                    <p class="mb-1 fw-bold text-dark">{{ $loan->repayment_progress }}% Complete</p>
                    <div class="progress" style="width: 250px; height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $loan->repayment_progress }}%;" aria-valuenow="{{ $loan->repayment_progress }}" aria-valuemin="0" aria-valuemax="100"></div>
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
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#repayments" role="tab">
                    <i class="bx bx-credit-card me-2 font-18"></i>Repayments
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#collaterals" role="tab">
                    <i class="bx bx-shield me-2 font-18"></i>Collaterals
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
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 text-dark fw-bold">Loan Information</h6>
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
                                ['label' => 'Total Repayments', 'value' => 'TZS ' . number_format($loan->repayments?->sum('amount') ?? 0, 2), 'icon' => 'bx bx-transfer']
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
                                        <button type="button" class="btn btn-primary w-100 d-flex align-items-center justify-content-center" onclick="approveLoan('{{ Hashids::encode($loan->id) }}')">
                                            <i class="bx bx-check-circle me-2"></i>
                                            <div class="text-start">
                                                <div class="fw-bold">{{ ucfirst($nextAction) }} Loan</div>
                                                <small class="d-block">{{ $nextRoleName }} (Level {{ $nextLevel }})</small>
                                            </div>
                                        </button>
                                    </div>
                                @endif

                                @if($loan->canBeRejected() && auth()->user() && $loan->canBeApprovedByUser(auth()->user()) && !$loan->hasUserApproved(auth()->user()))
                                    <div class="col-md-6 col-lg-4">
                                        <button type="button" class="btn btn-danger w-100 d-flex align-items-center justify-content-center" onclick="rejectLoan('{{ Hashids::encode($loan->id) }}')">
                                            <i class="bx bx-x-circle me-2"></i>
                                            <div class="text-start">
                                                <div class="fw-bold">Reject Loan</div>
                                                <small class="d-block">Decline Application</small>
                                            </div>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if(!auth()->user() || !$loan->canBeApprovedByUser(auth()->user()) || $loan->hasUserApproved(auth()->user()))
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    @if(!auth()->user())
                                        Please log in to perform approval actions.
                                    @elseif(!$loan->canBeApprovedByUser(auth()->user()))
                                        You don't have permission to approve this loan. Required role: {{ $nextRoleName }}
                                    @elseif($loan->hasUserApproved(auth()->user()))
                                        You have already approved this loan.
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
                                <div class="col-md-6 col-lg-4">
                                    <button type="button" class="btn btn-dark w-100 d-flex align-items-center justify-content-center" onclick="defaultLoan('{{ Hashids::encode($loan->id) }}')">
                                        <i class="bx bx-error-circle me-2"></i>
                                        <div class="text-start">
                                            <div class="fw-bold">Mark as Defaulted</div>
                                            <small class="d-block">Default Loan</small>
                                        </div>
                                    </button>
                                </div>
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
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary ps-4">Due Date</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Principal</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Interest</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary text-end pe-4">Total Installment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->schedule as $item)
                                    <tr>
                                        <td class="ps-4">{{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}</td>
                                        <td>{{ number_format($item->principal, 2) }}</td>
                                        <td>{{ number_format($item->interest, 2) }}</td>
                                        <td>{{ number_format($item->penalty_amount, 2) }}</td>
                                        <td>{{ number_format($item->fee_amount, 2) }}</td>
                                        <td class="text-end pe-4">{{ number_format($item->principal + $item->interest + $item->fee_amount + $item->penalty_amount, 2) }}</td>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Guarantors</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addGuarantorModal">
                        <i class="bx bx-user-plus me-2 font-18"></i>Add Guarantor
                    </button>
                </div>

                @if($loan->guarantors && $loan->guarantors->count())
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary ps-4">#</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Name</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Phone</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->guarantors as $index => $guarantor)
                                    <tr>
                                        <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                        <td>{{ $guarantor->name }}</td>
                                        <td>{{ $guarantor->phone }}</td>
                                        <td class="text-end pe-4">
                                            <form action="{{ route('loans.removeGuarantor', [$loan->id, $guarantor->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this guarantor?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
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
                    <h4 class="text-muted">No guarantors assigned to this loan.</h4>
                    <p class="text-secondary">Click the button above to add a guarantor.</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="documents" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Documents</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="bx bx-cloud-upload me-2 font-18"></i>Upload Document
                    </button>
                </div>

                @if($loan->loanFiles->count())
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary ps-4">#</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Document Name</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->loanFiles as $index => $doc)
                                    <tr>
                                        <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                        <td>{{ $doc->fileType->name }}</td>
                                        <td class="text-end pe-4">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary me-2">
                                                View
                                            </a>
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" download class="btn btn-sm btn-primary">
                                                Download
                                            </a>
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
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary ps-4">#</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Date</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Amount</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Type</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->repayments as $index => $repayment)
                                    <tr>
                                        <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                        <td>{{ \Carbon\Carbon::parse($repayment->payment_date)->format('M d, Y') }}</td>
                                        <td>{{ number_format($repayment->amount, 2) }}</td>
                                        <td>{{ ucfirst($repayment->payment_type ?? 'Regular') }}</td>
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
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary ps-4">#</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Type</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Description</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Value</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary text-end pe-4">Actions</th>
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
                    <div class="card-header bg-info text-white">
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

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#loansTableDetail').DataTable({
            responsive: true,
            order: [
                [1, 'asc']
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

        // Handle repayment form submission
        $('#repaymentForm').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Feature Not Implemented',
                text: 'Repayment functionality will be implemented soon.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
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
    });

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
</script>
@endpush