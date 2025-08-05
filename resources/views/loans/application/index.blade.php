@extends('layouts.main')

@section('title', 'Loan Applications')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
                ['label' => 'Loan Applications', 'url' => '#', 'icon' => 'bx bx-file-plus'],    
            ]" />
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">LOAN APPLICATIONS</h6>
                <a href="{{ route('loans.application.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Apply for Loan
                </a>
            </div>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Application ID</th>
                                            <th>Customer</th>
                                            <th>Product</th>
                                            <th>Amount</th>
                                            <th>Period</th>
                                            <th>Interest Rate</th>
                                            <th>Date Applied</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($loanApplications as $application)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">#{{ $application->id }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <i class="bx bx-user-circle fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $application->customer->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $application->customer->phone ?? 'No phone' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $application->product->name ?? 'No Product' }}</span>
                                                </td>
                                                <td>
                                                    <strong>TZS {{ number_format($application->amount, 2) }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $application->period }} months</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning">{{ $application->interest ?? 'N/A' }}%</span>
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($application->date_applied)->format('M d, Y') }}
                                                </td>
                                                <td>
                                                    @switch($application->status)
                                                        @case('applied')
                                                            <span class="badge bg-warning">Applied</span>
                                                            @break
                                                        @case('checked')
                                                            <span class="badge bg-info">Checked</span>
                                                            @break
                                                        @case('approved')
                                                            <span class="badge bg-primary">Approved</span>
                                                            @break
                                                        @case('authorized')
                                                            <span class="badge bg-success">Authorized</span>
                                                            @break
                                                        @case('active')
                                                            <span class="badge bg-success">Active</span>
                                                            @break
                                                        @case('rejected')
                                                            <span class="badge bg-danger">Rejected</span>
                                                            @break
                                                        @case('defaulted')
                                                            <span class="badge bg-dark">Defaulted</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                                                    @endswitch
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('loans.application.show', Hashids::encode($application->id)) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="View Details">view
                                                        </a>
                                                        @if($application->status === 'applied')
                                                            <a href="{{ route('loans.application.edit', Hashids::encode($application->id)) }}" 
                                                               class="btn btn-sm btn-outline-warning" 
                                                                    title="Edit Application">Edit
                                                            </a>
                                                        @endif
                                                        @if(!in_array($application->status, ['active', 'authorized']))
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-dark" 
                                                                    title="Delete Application"
                                                                    onclick="deleteApplication('{{ Hashids::encode($application->id) }}')">
                                                                Delete
                                                            </button>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Approval Actions -->
                                                    <x-loan-approval-actions :loan="$application" />
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-file-plus fs-1 mb-3"></i>
                                                        <h6>No Loan Applications Found</h6>
                                                        <p>Start by creating a new loan application.</p>
                                                        <a href="{{ route('loans.application.create') }}" class="btn btn-primary">
                                                            <i class="bx bx-plus me-1"></i> Apply for Loan
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($loanApplications->hasPages())
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $loanApplications->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
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
    function approveApplication(encodedId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');
        
        message.textContent = 'Are you sure you want to approve this loan application?';
        form.action = `/loans/application/${encodedId}/approve`;
        
        modal.show();
    }

    function rejectApplication(encodedId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');
        
        message.textContent = 'Are you sure you want to reject this loan application?';
        form.action = `/loans/application/${encodedId}/reject`;
        
        modal.show();
    }

    function deleteApplication(encodedId) {
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
                form.action = `/loans/application/${encodedId}`;
                
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