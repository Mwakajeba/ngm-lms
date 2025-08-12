@extends('layouts.main')

@section('title', 'Income Statement Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Income Statement Report', 'url' => '#', 'icon' => 'bx bx-line-chart']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-line-chart me-2"></i>Income Statement Report</h5>
                                <small class="text-muted">Generate income statement for the specified period</small>
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
                        <form id="incomeStatementForm" method="GET" action="{{ route('accounting.reports.income-statement') }}">
                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ $startDate }}" required>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ $endDate }}" required>
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

                        @if(isset($incomeStatementData))
                        <!-- Results -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">INCOME STATEMENT</h6>
                                                <small class="text-muted">
                                                    @if($startDate === $endDate)
                                                        As at: {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @else
                                                        Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @endif
                                                    Basis: {{ ucfirst($reportingType) }}
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
                                        @if(isset($incomeStatementData) && (count($incomeStatementData['data']['revenues'] ?? []) > 0 || count($incomeStatementData['data']['expenses'] ?? []) > 0))
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="4" style="text-align: center; font-weight:bold">INCOME STATEMENT</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Financial Statement Line Item</th>
                                                            <th>Ledger Account</th>
                                                            <th>Current Year</th>
                                                            <th>Comparative Period</th>
                                                        </tr>
                                                        <!-- Revenue Section -->
                                                        <tr class="line-item-header">
                                                            <td><b>Revenue</b></td>
                                                            <td colspan="3"></td>
                                                        </tr>
                                                        @php
                                                            $revenueTotalCurrent = 0;
                                                            $revenueTotalPrevious = 0;
                                                        @endphp

                                                        @foreach($incomeStatementData['data']['revenues'] as $group => $accounts)
                                                            @foreach($accounts as $account)
                                                                @php
                                                                    $previous = collect($incomeStatementData['data']['revenues_previous'][$group] ?? [])->firstWhere('account_id', $account['account_id'])['sum'] ?? 0;
                                                                @endphp

                                                                @if($account['sum'] != 0 || $previous != 0)
                                                                    @php
                                                                        $revenueTotalCurrent += $account['sum'];
                                                                        $revenueTotalPrevious += $previous;
                                                                    @endphp
                                                                    <tr>
                                                                        <td></td>
                                                                        <td>{{ $account['account_code'] }} - {{ $account['account'] }}</td>
                                                                        <td class="right-align">{{ number_format($account['sum'], 2) }}</td>
                                                                        <td class="right-align">{{ number_format($previous, 2) }}</td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @endforeach

                                                        <tr>
                                                            <td><b>Total Revenue</b></td>
                                                            <td></td>
                                                            <td class="right-align total"><b>{{ number_format($revenueTotalCurrent, 2) }}</b></td>
                                                            <td class="right-align total"><b>{{ number_format($revenueTotalPrevious, 2) }}</b></td>
                                                        </tr>

                                                        <!-- Expense Section -->
                                                        <tr class="line-item-header">
                                                            <td><b>Expenses</b></td>
                                                            <td colspan="3"></td>
                                                        </tr>
                                                        @php
                                                            $expenseTotalCurrent = 0;
                                                            $expenseTotalPrevious = 0;
                                                        @endphp

                                                        @foreach($incomeStatementData['data']['expenses'] as $group => $accounts)
                                                            @foreach($accounts as $account)
                                                                @php
                                                                    $previous = collect($incomeStatementData['data']['expenses_previous'][$group] ?? [])->firstWhere('account_id', $account['account_id'])['sum'] ?? 0;
                                                                @endphp

                                                                @if($account['sum'] != 0 || $previous != 0)
                                                                    @php
                                                                        $expenseTotalCurrent += $account['sum'];
                                                                        $expenseTotalPrevious += $previous;
                                                                    @endphp
                                                                    <tr>
                                                                        <td></td>
                                                                        <td>{{ $account['account_code'] }} - {{ $account['account'] }}</td>
                                                                        <td class="right-align">{{ number_format($account['sum'], 2) }}</td>
                                                                        <td class="right-align">{{ number_format($previous, 2) }}</td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @endforeach

                                                        <tr>
                                                            <td><b>Total Expenses</b></td>
                                                            <td></td>
                                                            <td class="right-align total"><b>{{ number_format($expenseTotalCurrent, 2) }}</b></td>
                                                            <td class="right-align total"><b>{{ number_format($expenseTotalPrevious, 2) }}</b></td>
                                                        </tr>

                                                        <!-- Net Income -->
                                                        <tr>
                                                            <td><b>Net Income</b></td>
                                                            <td></td>
                                                            <td class="right-align total">
                                                                <b>{{ number_format($revenueTotalCurrent - $expenseTotalCurrent, 2) }}</b>
                                                            </td>
                                                            <td class="right-align total">
                                                                <b>{{ number_format($revenueTotalPrevious - $expenseTotalPrevious, 2) }}</b>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="bx bx-info-circle fs-1 text-muted"></i>
                                                <p class="mt-2 text-muted">No income statement data found for the selected criteria.</p>
                                            </div>
                                        @endif
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
    document.getElementById('incomeStatementForm').submit();
}

function exportReport(type) {
    const form = document.getElementById('incomeStatementForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    const url = '{{ route("accounting.reports.income-statement.export") }}?' + new URLSearchParams(formData);
    
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