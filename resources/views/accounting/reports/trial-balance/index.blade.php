@extends('layouts.main')

@section('title', 'Trial Balance Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Trial Balance Report', 'url' => '#', 'icon' => 'bx bx-calculator']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-calculator me-2"></i>Trial Balance Report</h5>
                                <small class="text-muted">Generate trial balance for the specified period</small>
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
                        <!-- Filters -->
                        <form id="trialBalanceForm" method="GET" action="{{ route('accounting.reports.trial-balance') }}">
                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}" required>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}" required>
                                </div>

                                <!-- Reporting Type -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="reporting_type" class="form-label">Type</label>
                                    <select class="form-select" id="reporting_type" name="reporting_type">
                                        <option value="accrual" {{ $reportingType === 'accrual' ? 'selected' : '' }}>Accrual Basis</option>
                                        <option value="cash" {{ $reportingType === 'cash' ? 'selected' : '' }}>Cash Basis</option>
                                    </select>
                                </div>

                                <!-- Branch -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option value="all">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Layout -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="layout" class="form-label">Trial Balance Layout</label>
                                    <select class="form-select" id="layout" name="layout">
                                        <option value="single" {{ $layout === 'single' ? 'selected' : '' }}>Single Column</option>
                                        <option value="double" {{ $layout === 'double' ? 'selected' : '' }}>Double Column</option>
                                        <option value="multiple" {{ $layout === 'multiple' ? 'selected' : '' }}>Multiple Columns</option>
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

                        @if(isset($trialBalanceData))
                        <!-- Results -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">TRIAL BALANCE</h6>
                                                <small class="text-muted">
                                                    @if($startDate === $endDate)
                                                        As at: {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @else
                                                        Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @endif
                                                    Basis: {{ ucfirst($reportingType) }} | Layout: {{ ucfirst($layout) }}
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
                                        @if($trialBalanceData['data']->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <td colspan="{{ $layout === 'multiple' ? 9 : ($layout === 'double' ? 4 : 3) }}" 
                                                                class="text-center fw-bold fs-5">
                                                                {{ $user->company->name ?? 'SmartFinance' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="{{ $layout === 'multiple' ? 9 : ($layout === 'double' ? 4 : 3) }}" 
                                                                class="text-center fw-bold">
                                                                TRIAL BALANCE REPORT
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="{{ $layout === 'multiple' ? 9 : ($layout === 'double' ? 4 : 3) }}" 
                                                                class="text-center fw-bold">
                                                                @if($startDate === $endDate)
                                                                    AS AT {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                                                                @else
                                                                    FROM {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} TO {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                                                                @endif
                                                            </td>
                                                        </tr>

                                                        @if($layout === 'double')
                                                            <tr class="table-primary">
                                                                <th class="text-center">ACCOUNT NAME</th>
                                                                <th class="text-center">ACCOUNT CODE</th>
                                                                <th class="text-center">DEBIT</th>
                                                                <th class="text-center">CREDIT</th>
                                                            </tr>
                                                        @elseif($layout === 'single')
                                                            <tr class="table-primary">
                                                                <th class="text-center">ACCOUNT NAME</th>
                                                                <th class="text-center">ACCOUNT CODE</th>
                                                                <th class="text-center">BALANCE</th>
                                                            </tr>
                                                        @else
                                                            <tr class="table-primary">
                                                                <th rowspan="2" class="text-center align-middle">ACCOUNT NAME</th>
                                                                <th rowspan="2" class="text-center align-middle">ACCOUNT CODE</th>
                                                                <th colspan="2" class="text-center">OPENING BALANCES</th>
                                                                <th colspan="2" class="text-center">CURRENT YEAR CHANGES</th>
                                                                <th colspan="2" class="text-center">CLOSING BALANCES</th>
                                                                <th rowspan="2" class="text-center align-middle">DIFFERENCE</th>
                                                            </tr>
                                                            <tr class="table-primary">
                                                                <th class="text-center">DR</th>
                                                                <th class="text-center">CR</th>
                                                                <th class="text-center">DR</th>
                                                                <th class="text-center">CR</th>
                                                                <th class="text-center">DR</th>
                                                                <th class="text-center">CR</th>
                                                            </tr>
                                                        @endif
                                                    </thead>
                                                    <tbody>
                                                        @if($layout === 'double')
                                                            @php
                                                                $totalDebit = 0;
                                                                $totalCredit = 0;
                                                            @endphp
                                                            @foreach($trialBalanceData['data'] as $item)
                                                                @if($item->debit_total > 0 || $item->credit_total > 0)
                                                                    <tr>
                                                                        <td>{{ $item->account_name }}</td>
                                                                        <td class="text-monospace">{{ $item->account_code }}</td>
                                                                        <td class="text-end">
                                                                            @if($item->debit_total > 0)
                                                                                {{ number_format($item->debit_total, 2) }}
                                                                                @php $totalDebit += $item->debit_total; @endphp
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-end">
                                                                            @if($item->credit_total > 0)
                                                                                {{ number_format($item->credit_total, 2) }}
                                                                                @php $totalCredit += $item->credit_total; @endphp
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @elseif($layout === 'single')
                                                            @php
                                                                $totalDebit = 0;
                                                                $totalCredit = 0;
                                                            @endphp
                                                            @foreach($trialBalanceData['data'] as $item)
                                                                @if($item->balance != 0)
                                                                    <tr>
                                                                        <td>{{ $item->account_name }}</td>
                                                                        <td class="text-monospace">{{ $item->account_code }}</td>
                                                                        <td class="text-end {{ $item->balance < 0 ? 'text-danger' : 'text-success' }}">
                                                                            @if($item->balance < 0)
                                                                                ({{ number_format(abs($item->balance), 2) }})
                                                                                @php $totalCredit += abs($item->balance); @endphp
                                                                            @else
                                                                                {{ number_format($item->balance, 2) }}
                                                                                @php $totalDebit += $item->balance; @endphp
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            @php
                                                                $totalOpeningDr = 0;
                                                                $totalOpeningCr = 0;
                                                                $totalChangeDr = 0;
                                                                $totalChangeCr = 0;
                                                                $totalClosingDr = 0;
                                                                $totalClosingCr = 0;
                                                                $totalDiff = 0;
                                                            @endphp
                                                            
                                                            @foreach($trialBalanceData['data'] as $item)
                                                                @php
                                                                    $openingDr = 0; // Placeholder for opening debit
                                                                    $openingCr = 0; // Placeholder for opening credit
                                                                    $changeDr = $item->debit_total;
                                                                    $changeCr = $item->credit_total;
                                                                    $closingDr = $item->balance > 0 ? $item->balance : 0;
                                                                    $closingCr = $item->balance < 0 ? abs($item->balance) : 0;
                                                                    
                                                                    $openingDiff = $openingDr - $openingCr;
                                                                    $changeDiff = $changeDr - $changeCr;
                                                                    $closingDiff = $closingDr - $closingCr;
                                                                    $difference = $closingDiff;
                                                                    
                                                                    $totalOpeningDr += $openingDiff > 0 ? $openingDiff : 0;
                                                                    $totalOpeningCr += $openingDiff < 0 ? abs($openingDiff) : 0;
                                                                    
                                                                    $totalChangeDr += $changeDiff > 0 ? $changeDiff : 0;
                                                                    $totalChangeCr += $changeDiff < 0 ? abs($changeDiff) : 0;
                                                                    
                                                                    $totalClosingDr += $closingDiff > 0 ? $closingDiff : 0;
                                                                    $totalClosingCr += $closingDiff < 0 ? abs($closingDiff) : 0;
                                                                    
                                                                    $totalDiff += $difference;
                                                                @endphp
                                                                
                                                                @if($openingDiff != 0 || $changeDiff != 0 || $closingDiff != 0)
                                                                    <tr>
                                                                        <td>{{ $item->account_name }}</td>
                                                                        <td class="text-monospace">{{ $item->account_code }}</td>
                                                                        
                                                                        <td class="text-end">{{ $openingDiff > 0 ? number_format($openingDiff, 2) : '-' }}</td>
                                                                        <td class="text-end">{{ $openingDiff < 0 ? number_format(abs($openingDiff), 2) : '-' }}</td>
                                                                        
                                                                        <td class="text-end">{{ $changeDiff > 0 ? number_format($changeDiff, 2) : '-' }}</td>
                                                                        <td class="text-end">{{ $changeDiff < 0 ? number_format(abs($changeDiff), 2) : '-' }}</td>
                                                                        
                                                                        <td class="text-end">{{ $closingDiff > 0 ? number_format($closingDiff, 2) : '-' }}</td>
                                                                        <td class="text-end">{{ $closingDiff < 0 ? number_format(abs($closingDiff), 2) : '-' }}</td>
                                                                        
                                                                        <td class="text-end {{ $difference > 0 ? 'text-success' : ($difference < 0 ? 'text-danger' : '') }}">
                                                                            {{ $difference > 0 ? number_format($difference, 2) : ($difference < 0 ? number_format(abs($difference), 2) : '-') }}
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </tbody>
                                                    <tfoot class="table-warning">
                                                        @if($layout === 'double')
                                                            <tr class="fw-bold">
                                                                <td colspan="2" class="text-end">TOTAL</td>
                                                                <td class="text-end fw-bold">{{ number_format($totalDebit, 2) }}</td>
                                                                <td class="text-end fw-bold">{{ number_format($totalCredit, 2) }}</td>
                                                            </tr>
                                                            <tr class="fw-bold">
                                                                <td colspan="2" class="text-end">Net Balance (Debit - Credit)</td>
                                                                <td colspan="2" class="text-end {{ ($totalDebit - $totalCredit) == 0 ? 'text-success' : 'text-danger' }}">
                                                                    {{ number_format($totalDebit - $totalCredit, 2) }}
                                                                </td>
                                                            </tr>
                                                        @elseif($layout === 'single')
                                                            <tr class="fw-bold">
                                                                <td colspan="2" class="text-end">Net Balance (Debit - Credit)</td>
                                                                <td class="text-end {{ ($totalDebit - $totalCredit) == 0 ? 'text-success' : 'text-danger' }}">
                                                                    {{ number_format($totalDebit - $totalCredit, 2) }}
                                                                </td>
                                                            </tr>
                                                        @else
                                                            <tr class="fw-bold">
                                                                <td colspan="2" class="text-end">TOTAL</td>
                                                                <td class="text-end">{{ number_format($totalOpeningDr, 2) }}</td>
                                                                <td class="text-end">{{ number_format($totalOpeningCr, 2) }}</td>
                                                                <td class="text-end">{{ number_format($totalChangeDr, 2) }}</td>
                                                                <td class="text-end">{{ number_format($totalChangeCr, 2) }}</td>
                                                                <td class="text-end">{{ number_format($totalClosingDr, 2) }}</td>
                                                                <td class="text-end">{{ number_format($totalClosingCr, 2) }}</td>
                                                                <td class="text-end {{ $totalDiff > 0 ? 'text-success' : ($totalDiff < 0 ? 'text-danger' : '') }}">
                                                                    {{ $totalDiff > 0 ? number_format($totalDiff, 2) : ($totalDiff < 0 ? number_format(abs($totalDiff), 2) : '-') }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tfoot>
                                                </table>
                                            </div>

                                            <!-- Balance Check -->
                                            @php
                                                if($layout === 'double') {
                                                    $totalDebits = $totalDebit;
                                                    $totalCredits = $totalCredit;
                                                } elseif($layout === 'single') {
                                                    $totalDebits = $totalDebit;
                                                    $totalCredits = $totalCredit;
                                                } else {
                                                    $totalDebits = $totalClosingDr;
                                                    $totalCredits = $totalClosingCr;
                                                }
                                            @endphp
                                            <div class="alert alert-{{ ($totalDebits == $totalCredits) ? 'success' : 'danger' }} mt-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bx {{ ($totalDebits == $totalCredits) ? 'bx-check-circle' : 'bx-error' }} me-2"></i>
                                                    <div>
                                                        <strong>Trial Balance Check:</strong>
                                                        Debits ({{ number_format($totalDebits, 2) }}) = 
                                                        Credits ({{ number_format($totalCredits, 2) }})
                                                        @if($totalDebits == $totalCredits)
                                                            <br><small>✅ Trial balance is balanced</small>
                                                        @else
                                                            <br><small>⚠️ Trial balance is not balanced</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info">
                                                <i class="bx bx-info-circle me-2"></i>
                                                No trial balance data found for the specified criteria.
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
    document.getElementById('trialBalanceForm').submit();
}

function exportReport(type) {
    // Get form data
    const form = document.getElementById('trialBalanceForm');
    const formData = new FormData(form);
    
    // Create export URL with all parameters
    const params = new URLSearchParams(formData);
    params.append('export_type', type);
    
    // Use the export route
    const exportUrl = '{{ route("accounting.reports.trial-balance.export") }}?' + params.toString();
    
    // Open in new window for download
    window.open(exportUrl, '_blank');
}
</script>
@endsection 