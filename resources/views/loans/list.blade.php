@php
use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', $pageTitle ?? 'Loan Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => $pageTitle ?? 'Loan List', 'url' => '#', 'icon' => 'bx bx-list']
        ]" />
        <h6 class="mb-0 text-uppercase">{{ $pageTitle ?? 'LOAN LIST' }}</h6>
        <hr />

        <!-- Dashboard Stats -->
        <div class="row row-cols-1 row-cols-lg-4">
            <div class="col mb-4">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">{{ $pageTitle ?? 'Total Loans' }}</p>
                            <h4 class="mb-0" id="totalLoansCount">Loading...</h4>
                        </div>
                        <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loans Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        @can('create loan')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="card-title mb-0">{{ $pageTitle ?? 'Loans List' }}</h6>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="bx bx-import"></i> Import Loans
                                </button>
                                @if(isset($status) && $status === 'applied')
                                <a href="{{ route('loans.application.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> Create Loan Application
                                </a>
                                @else
                                <a href="{{ route('loans.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> Create Direct Loan
                                </a>
                                @endif
                            </div>
                        </div>
                        @endcan

                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap table-striped" id="loansTable">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>Amount</th>
                                        <th>Interest Rate</th>
                                        <th>Total Amount</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Branch</th>
                                        <th>Date Applied</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Loans</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Import Instructions:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Upload a CSV file with loan data</li>
                                            <li>Select loan type to determine chart account source</li>
                                            <li>Configure default settings for the import</li>
                                            <li>Maximum file size: 5MB</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="loan_type" class="form-label">Loan Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="loan_type" name="loan_type" required>
                                        <option value="">Select Loan Type</option>
                                        <option value="old">Old Loans</option>
                                        <option value="new">New Loans</option>
                                    </select>
                                    <div class="form-text">Determines chart account type (Old = Equity, New = Bank)</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                                    <div class="form-text">Supported: CSV, TXT (Max: 5MB)</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="default_branch" class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select class="form-select" id="default_branch" name="default_branch_id" required>
                                        <option value="">Select Branch</option>
                                        @if(isset($branches))
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ auth()->user()->branch_id == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="form-text">Branch for imported loans</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="loan_product" class="form-label">Loan Product <span class="text-danger">*</span></label>
                                    <select class="form-select" id="loan_product" name="loan_product_id" required>
                                        <option value="">Select Loan Product</option>
                                        @if(isset($loanProducts))
                                            @foreach($loanProducts as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="form-text">Default product for loans</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="loan_status" class="form-label">Loan Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="loan_status" name="loan_status" required>
                                        <option value="">Select Status</option>
                                        <option value="applied">Applied</option>
                                        <option value="checked">Checked</option>
                                        <option value="approved">Approved</option>
                                        <option value="authorized">Authorized</option>
                                        <option value="active" selected>Active</option>
                                        <option value="defaulted">Defaulted</option>
                                    </select>
                                    <div class="form-text">Status for imported loans</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="chart_account" class="form-label">Chart Account <span class="text-danger">*</span></label>
                                    <select class="form-select" id="chart_account" name="chart_account_id" required disabled>
                                        <option value="">Select loan type first</option>
                                    </select>
                                    <div class="form-text" id="chart_account_help">Select loan type to see accounts</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="skip_errors" name="skip_errors" checked>
                                        <label class="form-check-label" for="skip_errors">
                                            Skip rows with errors and continue import
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="me-auto">
                                <a href="#" class="btn btn-outline-secondary btn-sm" id="downloadTemplate">
                                    <i class="bx bx-download"></i> Download Sample Template
                                </a>
                            </div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-import"></i> Import Loans
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const currentStatus = '{{ $status ?? "active" }}';
        
        // Initialize DataTable with Ajax
        const table = $('#loansTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route("loans.data") }}',
                data: function(d) {
                    d.status = currentStatus;
                },
                error: function(xhr, error, code) {
                    console.log('Ajax error:', xhr.responseJSON);
                }
            },
            columns: [
                { data: 'customer_name', name: 'customer_name', orderable: false },
                { data: 'product_name', name: 'product_name' },
                { data: 'formatted_amount', name: 'amount' },
                { data: 'interest_display', name: 'interest' },
                { data: 'formatted_total', name: 'amount_total' },
                { data: 'period', name: 'period' },
                { data: 'status_badge', name: 'status', orderable: false },
                { data: 'branch_name', name: 'branch_name' },
                { data: 'formatted_date', name: 'date_applied' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[8, 'desc']], // Order by date applied descending
            pageLength: 25,
            language: {
                search: "",
                searchPlaceholder: "Search loans...",
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>'
            },
            columnDefs: [
                {
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                    responsivePriority: 1
                },
                {
                    targets: [-1],
                    responsivePriority: 1,
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function(settings) {
                // Update total count
                $('#totalLoansCount').text(settings.json.recordsTotal || 0);
                
                // Reinitialize delete buttons
                $('.delete-btn').off('click').on('click', function() {
                    const loanId = $(this).data('id');
                    const loanName = $(this).data('name');
                    deleteLoan(loanId, loanName);
                });
            }
        });

        // Global error handler for Ajax requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Handle import form submission
        $('#importForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            // Validate required fields
            if (!$('#loan_type').val() || !$('#csv_file').val() || !$('#default_branch').val() || 
                !$('#loan_product').val() || !$('#loan_status').val() || !$('#chart_account').val()) {
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please fill in all required fields before importing.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Importing...');
            
            // For now, just show a placeholder message
            Swal.fire({
                title: 'Import Configuration',
                html: `
                    <div class="text-start">
                        <p><strong>Loan Type:</strong> ${$('#loan_type option:selected').text()}</p>
                        <p><strong>Branch:</strong> ${$('#default_branch option:selected').text()}</p>
                        <p><strong>Product:</strong> ${$('#loan_product option:selected').text()}</p>
                        <p><strong>Status:</strong> ${$('#loan_status option:selected').text()}</p>
                        <p><strong>Chart Account:</strong> ${$('#chart_account option:selected').text()}</p>
                        <p><strong>File:</strong> ${$('#csv_file')[0].files[0].name}</p>
                    </div>
                    <br>
                    <p>Import functionality will be implemented based on these settings.</p>
                `,
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
                $('#importModal').modal('hide');
            });
        });

        // Handle loan type change to load appropriate chart accounts
        $('#loan_type').on('change', function() {
            const loanType = $(this).val();
            const chartAccountSelect = $('#chart_account');
            const helpText = $('#chart_account_help');
            
            if (!loanType) {
                chartAccountSelect.prop('disabled', true).html('<option value="">Select loan type first</option>');
                helpText.text('Select loan type to see accounts');
                return;
            }
            
            // Enable the select and show loading
            chartAccountSelect.prop('disabled', false).html('<option value="">Loading accounts...</option>');
            
            // Simulate loading chart accounts based on type
            setTimeout(() => {
                if (loanType === 'old') {
                    chartAccountSelect.html(`
                        <option value="">Select Equity Account</option>
                        <option value="eq1">Retained Earnings</option>
                        <option value="eq2">Share Capital</option>
                        <option value="eq3">Equity Reserve</option>
                        <option value="eq4">Other Equity Accounts</option>
                    `);
                    helpText.text('Equity accounts for old loans');
                } else if (loanType === 'new') {
                    chartAccountSelect.html(`
                        <option value="">Select Bank Account</option>
                        <option value="ba1">Main Bank Account</option>
                        <option value="ba2">Savings Account</option>
                        <option value="ba3">Current Account</option>
                        <option value="ba4">Loan Disbursement Account</option>
                    `);
                    helpText.text('Bank accounts for new loans');
                }
            }, 500);
        });

        // Handle download template
        $('#downloadTemplate').on('click', function(e) {
            e.preventDefault();
            
            // For now, show info about template
            Swal.fire({
                title: 'Sample Template',
                text: 'The sample CSV template will be generated with the required columns: customer_id, product_id, amount, interest, period, date_applied, sector, etc.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        });
    });

    function deleteLoan(encodedId, customerName) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the loan for ${customerName}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/loans/${encodedId}`;

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