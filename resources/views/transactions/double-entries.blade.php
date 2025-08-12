@extends('layouts.main')

@section('title', 'Double Entries - ' . $account_name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Double Entries - ' . $account_name, 'url' => '#', 'icon' => 'bx bx-list-ul']
        ]" />

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">Double Entries</h5>
                                </div>
                                <p class="mb-0 text-muted">All transactions for {{ $account_name }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Account Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Account Name</label>
                                    <p class="form-control-plaintext">{{ $account_name }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Account Code</label>
                                    <p class="form-control-plaintext">{{ $chartAccount->account_code ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Account Class</label>
                                    <p class="form-control-plaintext">{{ $chartAccount->accountClassGroup->accountClass->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Account Group</label>
                                    <p class="form-control-plaintext">{{ $chartAccount->accountClassGroup->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-lg-3">
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Debit</p>
                                <h4 class="font-weight-bold text-success">{{ number_format($totalDebit, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white">
                                <i class='bx bx-trending-up'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Credit</p>
                                <h4 class="font-weight-bold text-danger">{{ number_format($totalCredit, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-danger text-white">
                                <i class='bx bx-trending-down'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Balance</p>
                                <h4 class="font-weight-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($balance, 2) }}
                                </h4>
                            </div>
                            <div class="widgets-icons {{ $balance >= 0 ? 'bg-gradient-success' : 'bg-gradient-danger' }} text-white">
                                <i class='bx bx-balance'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Transaction History</h6>
                    </div>
                    <div class="card-body">
                        <!-- Help Information -->
                        <div class="alert alert-info mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-info-circle me-2"></i>
                                <div>
                                    <strong>Tip:</strong> Click on any <strong class="text-success">Debit</strong> or <strong class="text-danger">Credit</strong> amount to view the complete double entry details for that transaction.
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table id="doubleEntriesTable" class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        <th class="text-end">Running Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transactionData)
                                    @php $transaction = $transactionData['transaction']; @endphp
                                    <tr>
                                        <td>{{ $transaction->date ? $transaction->date->format('d-m-Y') : 'N/A' }}</td>
                                        <td>
                                            @if($transaction->journal)
                                                <a href="{{ route('accounting.journals.show', $transaction->journal) }}" class="text-decoration-none">
                                                    {{ $transaction->journal->reference }}
                                                </a>
                                            @elseif($transaction->paymentVoucher)
                                                <a href="{{ route('accounting.payment-vouchers.show', $transaction->paymentVoucher) }}" class="text-decoration-none">
                                                    {{ $transaction->paymentVoucher->reference }}
                                                </a>
                                            @elseif($transaction->bill)
                                                <a href="{{ route('accounting.bill-purchases.show', $transaction->bill) }}" class="text-decoration-none">
                                                    {{ $transaction->bill->reference }}
                                                </a>
                                            @else
                                                {{ $transaction->transaction_id }}
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($transaction->description, 50) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->transaction_type == 'journal' ? 'primary' : ($transaction->transaction_type == 'payment' ? 'success' : 'warning') }}">
                                                {{ ucfirst($transaction->transaction_type) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($transactionData['debit_amount'] > 0)
                                                <a href="{{ route('accounting.transactions.details', Hashids::encode($transaction->id), $transaction->transaction_type) }}" 
                                                   class="text-decoration-none fw-bold text-success">
                                                    {{ number_format($transactionData['debit_amount'], 2) }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($transactionData['credit_amount'] > 0)
                                                <a href="{{ route('accounting.transactions.details', Hashids::encode($transaction->id), $transaction->transaction_type) }}" 
                                                   class="text-decoration-none fw-bold text-danger">
                                                    {{ number_format($transactionData['credit_amount'], 2) }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold {{ $transactionData['running_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($transactionData['running_balance'], 2) }}
                                            </span>
                                        </td>
                                      
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle me-2"></i>No transactions found for this account
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Summary -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Account Balance Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="alert alert-info mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-trending-up me-2"></i>
                                        <div>
                                            <strong>Total Debits:</strong> {{ number_format($totalDebit, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-trending-down me-2"></i>
                                        <div>
                                            <strong>Total Credits:</strong> {{ number_format($totalCredit, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-{{ $balance == 0 ? 'success' : 'danger' }} mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx {{ $balance == 0 ? 'bx-check-circle' : 'bx-error' }} me-2"></i>
                                        <div>
                                            <strong>Final Balance:</strong> {{ number_format($balance, 2) }}
                                            @if($balance == 0)
                                                <br><small>✅ Account is balanced</small>
                                            @else
                                                <br><small>⚠️ Account is not balanced</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item {
    margin-bottom: 1rem;
}

.info-item label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-item p {
    font-size: 1rem;
    color: #212529;
    margin-bottom: 0;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable for Double Entries
    $('#doubleEntriesTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[0, 'asc']], // Sort by date ascending
        columnDefs: [
            {
                targets: [4, 5, 6], // Debit, Credit, Running Balance columns
                className: 'text-end'
            },
            {
                targets: [7], // Actions column
                className: 'text-center',
                orderable: false
            },
            {
                targets: [3], // Type column
                orderable: true,
                searchable: true
            }
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "Search transactions:",
            lengthMenu: "Show _MENU_ transactions per page",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        initComplete: function() {
            // Add custom filters
            this.api().columns().every(function() {
                var column = this;
                var title = column.header().textContent;
                
                // Add filter for transaction type
                if (title === 'Type') {
                    var select = $('<select class="form-select form-select-sm ms-2"><option value="">All Types</option></select>')
                        .appendTo($(column.header()))
                        .on('change', function() {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());
                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                        });
                    
                    column.data().unique().sort().each(function(d, j) {
                        select.append('<option value="' + d + '">' + d + '</option>');
                    });
                }
            });
        }
    });
});

// JavaScript functions for transaction actions
function viewTransactionDetails(transactionId, transactionType) {
    // Show loading state
    Swal.fire({
        title: 'Loading...',
        text: 'Loading transaction details',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Redirect to transaction details
    window.location.href = `/accounting/transactions/details/${transactionId}/${transactionType}`;
}

function viewRelatedTransactions(transactionId, transactionType) {
    // Show loading state
    Swal.fire({
        title: 'Loading...',
        text: 'Loading related transactions',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Redirect to related transactions
    window.location.href = `/accounting/transactions/details/${transactionId}/${transactionType}`;
}

// Add export functionality
function exportDoubleEntries() {
    var table = $('#doubleEntriesTable').DataTable();
    var data = table.data().toArray();
    
    // Create CSV content
    var csv = 'Date,Reference,Description,Type,Debit,Credit,Running Balance\n';
    data.forEach(function(row) {
        csv += row.join(',') + '\n';
    });
    
    // Download CSV file
    var blob = new Blob([csv], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'double-entries.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Add print functionality
function printDoubleEntries() {
    var table = $('#doubleEntriesTable').DataTable();
    table.page.len(-1).draw(); // Show all records
    window.print();
    table.page.len(25).draw(); // Reset to default page length
}
</script>
@endsection 