@extends('layouts.main')

@section('title', 'Accounting Notes Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Accounting Notes Report', 'url' => '#', 'icon' => 'bx bx-note']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-note me-2"></i>Accounting Notes Report</h5>
                                <small class="text-muted">Generate accounting notes and policies report</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" onclick="generateReport()">
                                    <i class="bx bx-refresh me-1"></i> Generate Report
                                </button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                            <i class="bx bx-file-pdf me-2"></i> Export PDF
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">
                                            <i class="bx bx-file me-2"></i> Export Excel
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <form id="accountingNotesForm" method="GET" action="{{ route('accounting.reports.accounting-notes') }}">
                            <div class="row">
                                <!-- As of Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="as_of_date" class="form-label">As of Date</label>
                                    <input type="date" class="form-control" id="as_of_date" name="as_of_date" 
                                           value="{{ $asOfDate }}" required>
                                </div>

                                <!-- Reporting Type -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="reporting_type" class="form-label">Reporting Type</label>
                                    <select class="form-select" id="reporting_type" name="reporting_type" required>
                                        <option value="accrual" {{ $reportingType === 'accrual' ? 'selected' : '' }}>Accrual Basis</option>
                                        <option value="cash" {{ $reportingType === 'cash' ? 'selected' : '' }}>Cash Basis</option>
                                    </select>
                                </div>

                                <!-- Branch (Admin Only) -->
                                @if($user->hasRole('admin'))
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- Level of Detail -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="level_of_detail" class="form-label">Level of Detail</label>
                                    <select class="form-select" id="level_of_detail" name="level_of_detail">
                                        <option value="summary" {{ $levelOfDetail === 'summary' ? 'selected' : '' }}>Summary</option>
                                        <option value="detailed" {{ $levelOfDetail === 'detailed' ? 'selected' : '' }}>Detailed</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bx bx-search me-1"></i>Generate Report
                                    </button>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-download me-1"></i>Export
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                                <i class="bx bx-file-pdf me-2"></i>Export PDF
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">
                                                <i class="bx bx-file me-2"></i>Export Excel
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </form>

                        @if(isset($accountingNotesData))
                        <!-- Results -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">ACCOUNTING NOTES</h6>
                                                <small class="text-muted">
                                                    As at: {{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }} | 
                                                    Basis: {{ ucfirst($reportingType) }} | 
                                                    Detail: {{ ucfirst($levelOfDetail) }}
                                                </small>
                                            </div>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportReport('pdf')">
                                                    <i class="bx bx-file-pdf me-1"></i>PDF
                                                </button>
                                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportReport('excel')">
                                                    <i class="bx bx-file me-1"></i>Excel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead class="table-light">
                                                    <tr>
                                                        <td colspan="2" class="text-center fw-bold fs-5">
                                                            {{ $user->company->name ?? 'SmartFinance' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="text-center fw-bold">
                                                            ACCOUNTING NOTES
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="text-center fw-bold">
                                                            AS AT {{ \Carbon\Carbon::parse($asOfDate)->format('d-m-Y') }}
                                                        </td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- 1. Significant Accounting Policies -->
                                                    <tr class="table-primary">
                                                        <td colspan="2" class="fw-bold">1. SIGNIFICANT ACCOUNTING POLICIES</td>
                                                    </tr>
                                                    
                                                    @foreach($accountingNotesData['accounting_policies'] as $policy => $details)
                                                        <tr class="table-secondary">
                                                            <td colspan="2" class="fw-bold">{{ $policy }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">{{ $details['description'] }}</td>
                                                        </tr>
                                                        @foreach($details['details'] as $detail)
                                                            <tr>
                                                                <td width="5%"></td>
                                                                <td>• {{ $detail }}</td>
                                                            </tr>
                                                        @endforeach
                                                        <tr><td colspan="2"></td></tr>
                                                    @endforeach

                                                    <!-- 2. Significant Transactions -->
                                                    <tr class="table-primary">
                                                        <td colspan="2" class="fw-bold">2. SIGNIFICANT TRANSACTIONS</td>
                                                    </tr>
                                                    
                                                    @if(count($accountingNotesData['significant_transactions']) > 0)
                                                        <tr class="table-secondary">
                                                            <th>Date</th>
                                                            <th>Account</th>
                                                        </tr>
                                                        @foreach($accountingNotesData['significant_transactions'] as $transaction)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}</td>
                                                                <td>{{ $transaction->account_name }} - {{ number_format($transaction->amount, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="2">No significant transactions during the period.</td>
                                                        </tr>
                                                    @endif

                                                    <!-- 3. Contingent Liabilities -->
                                                    <tr class="table-primary">
                                                        <td colspan="2" class="fw-bold">3. CONTINGENT LIABILITIES</td>
                                                    </tr>
                                                    
                                                    @foreach($accountingNotesData['contingent_liabilities'] as $liability)
                                                        <tr class="table-secondary">
                                                            <td colspan="2" class="fw-bold">{{ $liability['description'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">{{ $liability['notes'] }}</td>
                                                        </tr>
                                                        <tr><td colspan="2"></td></tr>
                                                    @endforeach

                                                    <!-- 4. Related Party Transactions -->
                                                    <tr class="table-primary">
                                                        <td colspan="2" class="fw-bold">4. RELATED PARTY TRANSACTIONS</td>
                                                    </tr>
                                                    
                                                    @foreach($accountingNotesData['related_party_transactions'] as $transaction)
                                                        <tr class="table-secondary">
                                                            <td colspan="2" class="fw-bold">{{ $transaction['party_name'] }} - {{ $transaction['transaction_type'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">{{ $transaction['notes'] }}</td>
                                                        </tr>
                                                        <tr><td colspan="2"></td></tr>
                                                    @endforeach

                                                    <!-- 5. Post-Balance Sheet Events -->
                                                    <tr class="table-primary">
                                                        <td colspan="2" class="fw-bold">5. POST-BALANCE SHEET EVENTS</td>
                                                    </tr>
                                                    
                                                    @foreach($accountingNotesData['post_balance_sheet_events'] as $event)
                                                        <tr class="table-secondary">
                                                            <td colspan="2" class="fw-bold">{{ $event['event_description'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">{{ $event['notes'] }}</td>
                                                        </tr>
                                                        <tr><td colspan="2"></td></tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport() {
    document.getElementById('accountingNotesForm').submit();
}

function exportReport(type) {
    const form = document.getElementById('accountingNotesForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    const url = '{{ route("accounting.reports.accounting-notes.export") }}?' + new URLSearchParams(formData);
    
    // Show loading state
    Swal.fire({
        title: 'Generating Report...',
        text: 'Please wait while we prepare your ' + type.toUpperCase() + ' report.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Download the file
    window.location.href = url;
}
</script>
@endsection 