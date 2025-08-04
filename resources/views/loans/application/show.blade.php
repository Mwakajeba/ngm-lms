@extends('layouts.main')

@section('title', 'Loan Application Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
                ['label' => 'Loan Applications', 'url' => route('loans.application.index'), 'icon' => 'bx bx-file-plus'],
                ['label' => 'Application Details', 'url' => '#', 'icon' => 'bx bx-show'],    
            ]" />
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">LOAN APPLICATION DETAILS</h6>
                <div>
                    <a href="{{ route('loans.application.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Applications
                    </a>
                    @if($loanApplication->status === 'pending')
                        <a href="{{ route('loans.application.edit', Hashids::encode($loanApplication->id)) }}" class="btn btn-warning">
                            <i class="bx bx-edit me-1"></i> Edit Application
                        </a>
                    @endif
                </div>
            </div>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Application Details -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bx bx-file-plus me-2"></i>Application Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Application ID:</label>
                                    <p class="mb-0">
                                        <span class="badge bg-primary">#{{ $loanApplication->id }}</span>
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status:</label>
                                    <p class="mb-0">
                                        @switch($loanApplication->status)
                                            @case('pending')
                                                <span class="badge bg-warning">Pending</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-success">Approved</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                                @break
                                            @case('active')
                                                <span class="badge bg-primary">Active</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($loanApplication->status) }}</span>
                                        @endswitch
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Date Applied:</label>
                                    <p class="mb-0">{{ \Carbon\Carbon::parse($loanApplication->date_applied)->format('M d, Y') }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Business Sector:</label>
                                    <p class="mb-0">{{ $loanApplication->sector }}</p>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Loan Amount:</label>
                                    <p class="mb-0 fs-5 text-primary fw-bold">TZS {{ number_format($loanApplication->amount, 2) }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Interest Rate:</label>
                                    <p class="mb-0 fs-5 text-warning fw-bold">{{ $loanApplication->interest }}%</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Loan Period:</label>
                                    <p class="mb-0">{{ $loanApplication->period }} months</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Loan Product:</label>
                                    <p class="mb-0">
                                        <span class="badge bg-info">{{ $loanApplication->product->name }}</span>
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Loan Purpose:</label>
                                    <p class="mb-0">{{ $loanApplication->purpose ?? 'Not specified' }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Collateral/Security:</label>
                                    <p class="mb-0">{{ $loanApplication->collateral ?? 'No collateral specified' }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Additional Notes:</label>
                                    <p class="mb-0">{{ $loanApplication->notes ?? 'No additional notes' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer & Account Information -->
                <div class="col-lg-4">
                    <!-- Customer Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bx bx-user me-2"></i>Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-lg me-3">
                                    <i class="bx bx-user-circle fs-1"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $loanApplication->customer->name }}</h6>
                                    <small class="text-muted">{{ $loanApplication->customer->phone ?? 'No phone' }}</small>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">Email:</small>
                                <p class="mb-1">{{ $loanApplication->customer->email ?? 'No email' }}</p>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">Address:</small>
                                <p class="mb-1">{{ $loanApplication->customer->address ?? 'No address' }}</p>
                            </div>

                            @if($loanApplication->group)
                            <div class="mb-2">
                                <small class="text-muted">Group:</small>
                                <p class="mb-1">
                                    <span class="badge bg-secondary">{{ $loanApplication->group->name }}</span>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Disbursement Account -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bx bx-bank me-2"></i>Disbursement Account</h6>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-2">{{ $loanApplication->bankAccount->name }}</h6>
                            <p class="mb-1">
                                <small class="text-muted">Account Number:</small><br>
                                {{ $loanApplication->bankAccount->account_number ?? 'No account number' }}
                            </p>
                            <p class="mb-0">
                                <small class="text-muted">Bank:</small><br>
                                {{ $loanApplication->bankAccount->bank_name ?? 'No bank name' }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($loanApplication->status === 'pending')
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success" onclick="approveApplication('{{ Hashids::encode($loanApplication->id) }}')">
                                    <i class="bx bx-check me-1"></i> Approve Application
                                </button>
                                <button type="button" class="btn btn-danger" onclick="rejectApplication('{{ Hashids::encode($loanApplication->id) }}')">
                                    <i class="bx bx-x me-1"></i> Reject Application
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if(!in_array($loanApplication->status, ['active', 'authorized']))
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bx bx-trash me-2"></i>Delete Action</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-dark" onclick="deleteApplication('{{ Hashids::encode($loanApplication->id) }}')">
                                    <i class="bx bx-trash me-1"></i> Delete Application
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="approvalForm" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function approveApplication(applicationId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');
        
        message.textContent = 'Are you sure you want to approve this loan application? This will create an active loan.';
        form.action = `/loans/application/${applicationId}/approve`;
        
        modal.show();
    }

    function rejectApplication(applicationId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');
        
        message.textContent = 'Are you sure you want to reject this loan application?';
        form.action = `/loans/application/${applicationId}/reject`;
        
        modal.show();
    }

    function deleteApplication(applicationId) {
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
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/loans/application/${applicationId}`;
                
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