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

                                <!-- Level of Detail -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="level_of_detail" class="form-label">Level of Detail</label>
                                    <select class="form-select" id="level_of_detail" name="level_of_detail">
                                        <option value="summary" {{ $levelOfDetail === 'summary' ? 'selected' : '' }}>Summary</option>
                                        <option value="detailed" {{ $levelOfDetail === 'detailed' ? 'selected' : '' }}>Detailed</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Dynamic Comparative Columns -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="bx bx-plus-circle me-2"></i>Comparative Columns</h6>
                                        <button type="button" class="btn btn-success btn-sm" onclick="addComparativeColumn()">
                                            <i class="bx bx-plus me-1"></i> Add Comparative Column
                                        </button>
                                    </div>
                                    <small class="text-muted">Add comparative periods to compare trial balances across different time periods</small>
                                </div>
                            </div>

                            <div id="comparativeColumnsContainer">
                                @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                    @foreach($comparativeColumns as $index => $column)
                                        <div class="row mb-2 comparative-column" data-index="{{ $index }}">
                                            <div class="col-md-5">
                                                <label class="form-label">Start Date</label>
                                                <input type="date" class="form-control" name="comparative_columns[{{ $index }}][start_date]" value="{{ $column['start_date'] }}" required>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">End Date</label>
                                                <input type="date" class="form-control" name="comparative_columns[{{ $index }}][end_date]" value="{{ $column['end_date'] }}" required>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeComparativeColumn(this)">
                                                    <i class="bx bx-trash me-1"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bx bx-search me-1"></i>Generate Report
                                    </button>
                                    <a href="{{ route('accounting.reports.trial-balance') }}" class="btn btn-outline-secondary ms-2">
                                        <i class="bx bx-reset me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Report Results -->
                        @if(isset($trialBalanceData))
                        <div class="row">
                            <div class="col-12">
                                <!-- Report Header -->
                                <div class="text-center mb-4">
                                    <h4 class="mb-1"><strong>{{ $user->company->name ?? 'Company Name' }}</strong></h4>
                                    <h6 class="text-muted mb-2">TRIAL BALANCE</h6>
                                    @if($startDate == $endDate)
                                        <p class="mb-1">As at {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }}</p>
                                    @else
                                        <p class="mb-1">From {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</p>
                                    @endif
                                    @if($branchId && $branchId != 'all')
                                        <p class="mb-1 text-muted">Branch: {{ $branches->where('id', $branchId)->first()->name ?? 'N/A' }}</p>
                                    @endif
                                    <p class="mb-0 text-muted small">
                                        {{ ucfirst($reportingType) }} Basis | 
                                        {{ ucfirst($layout) }} Layout |
                                        Generated on {{ now()->format('F d, Y \a\t g:i A') }}
                                    </p>
                                </div>
                                
                                <!-- Trial Balance Table -->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Account/Group</th>
                                                        @if($levelOfDetail === 'detailed')
                                                            <th>Code</th>
                                                        @endif
                                                        <th class="text-end">Debit</th>
                                                        <th class="text-end">Credit</th>
                                                        @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                            @foreach($comparativeColumns as $columnIndex => $column)
                                                                <th class="text-end">Comparative</th>
                                                                <th class="text-end">Comparative</th>
                                                            @endforeach
                                                        @endif
                                                    </tr>
                                                    <tr>
                                                        <th></th>
                                                        @if($levelOfDetail === 'detailed')
                                                            <th></th>
                                                        @endif
                                                        <th class="text-end">Current Period</th>
                                                        <th class="text-end">Current Period</th>
                                                        @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                            @foreach($comparativeColumns as $column)
                                                                <th class="text-end">Debit</th>
                                                                <th class="text-end">Credit</th>
                                                            @endforeach
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php 
                                                        $totalDebit = 0; 
                                                        $totalCredit = 0;
                                                    @endphp
                                                    @foreach($trialBalanceData['data'] as $item)
                                                        @php 
                                                            $debitAmount = $item->debit_total;
                                                            $creditAmount = $item->credit_total;
                                                            $totalDebit += $debitAmount;
                                                            $totalCredit += $creditAmount;
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                @if($levelOfDetail === 'detailed')
                                                                    <strong>{{ $item->account_name }}</strong>
                                                                @else
                                                                    <strong>{{ $item->group_name }}</strong>
                                                                @endif
                                                            </td>
                                                            @if($levelOfDetail === 'detailed')
                                                                <td>{{ $item->account_code }}</td>
                                                            @endif
                                                            <td class="text-end">
                                                                @if($debitAmount > 0)
                                                                    <span class="text-danger">{{ number_format($debitAmount, 2) }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-end">
                                                                @if($creditAmount > 0)
                                                                    <span class="text-success">{{ number_format($creditAmount, 2) }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            @if($layout === 'double')
                                                                <td class="text-end">
                                                                    <span class="text-muted">-</span>
                                                                </td>
                                                                <td class="text-end">
                                                                    <span class="text-muted">-</span>
                                                                </td>
                                                            @endif
                                                            @if($layout === 'multiple')
                                                                <td class="text-end">
                                                                    <span class="text-muted">-</span>
                                                                </td>
                                                                <td class="text-end">
                                                                    <span class="text-muted">-</span>
                                                                </td>
                                                                <td class="text-end">
                                                                    <span class="text-muted">-</span>
                                                                </td>
                                                                <td class="text-end">
                                                                    <span class="text-muted">-</span>
                                                                </td>
                                                            @endif
                                                            @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                                @foreach($comparativeColumns as $columnIndex => $column)
                                                                    @php
                                                                        // Get comparative data for this column
                                                                        $comparativeData = $trialBalanceData['comparative']['Comparative ' . ($columnIndex + 1)] ?? [];
                                                                        $comparativeItem = collect($comparativeData)->first(function($comp) use ($item, $levelOfDetail) {
                                                                            if (!$comp) return false;
                                                                            return $levelOfDetail === 'detailed' 
                                                                                ? (isset($comp->account_id) && isset($item->account_id) && $comp->account_id == $item->account_id)
                                                                                : (isset($comp->group_id) && isset($item->group_id) && $comp->group_id == $item->group_id);
                                                                        });
                                                                        $comparativeDebit = $comparativeItem ? $comparativeItem->debit_total : 0;
                                                                        $comparativeCredit = $comparativeItem ? $comparativeItem->credit_total : 0;
                                                                    @endphp
                                                                    <td class="text-end">
                                                                        @if($comparativeDebit > 0)
                                                                            <span class="text-muted">{{ number_format($comparativeDebit, 2) }}</span>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-end">
                                                                        @if($comparativeCredit > 0)
                                                                            <span class="text-muted">{{ number_format($comparativeCredit, 2) }}</span>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                    <tr class="table-primary">
                                                        <td><strong>TOTAL</strong></td>
                                                        @if($levelOfDetail === 'detailed')
                                                            <td></td>
                                                        @endif
                                                        <td class="text-end">
                                                            <strong class="text-danger">{{ number_format($totalDebit, 2) }}</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong class="text-success">{{ number_format($totalCredit, 2) }}</strong>
                                                        </td>
                                                        @if($layout === 'double')
                                                            <td class="text-end">
                                                                <strong class="text-muted">-</strong>
                                                            </td>
                                                            <td class="text-end">
                                                                <strong class="text-muted">-</strong>
                                                            </td>
                                                        @endif
                                                        @if($layout === 'multiple')
                                                            <td class="text-end">
                                                                <strong class="text-muted">-</strong>
                                                            </td>
                                                            <td class="text-end">
                                                                <strong class="text-muted">-</strong>
                                                            </td>
                                                            <td class="text-end">
                                                                <strong class="text-muted">-</strong>
                                                            </td>
                                                            <td class="text-end">
                                                                <strong class="text-muted">-</strong>
                                                            </td>
                                                        @endif
                                                        @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                            @foreach($comparativeColumns as $columnIndex => $column)
                                                                @php
                                                                    $comparativeData = $trialBalanceData['comparative']['Comparative ' . ($columnIndex + 1)] ?? [];
                                                                    $comparativeTotalDebit = collect($comparativeData)->sum('debit_total');
                                                                    $comparativeTotalCredit = collect($comparativeData)->sum('credit_total');
                                                                @endphp
                                                                <td class="text-end">
                                                                    <strong class="text-muted">{{ number_format($comparativeTotalDebit, 2) }}</strong>
                                                                </td>
                                                                <td class="text-end">
                                                                    <strong class="text-muted">{{ number_format($comparativeTotalCredit, 2) }}</strong>
                                                                </td>
                                                            @endforeach
                                                        @endif
                                                    </tr>
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
let comparativeColumnIndex = {{ isset($comparativeColumns) ? count($comparativeColumns) : 0 }};

function addComparativeColumn() {
    const container = document.getElementById('comparativeColumnsContainer');
    const newColumn = document.createElement('div');
    newColumn.className = 'row mb-2 comparative-column';
    newColumn.setAttribute('data-index', comparativeColumnIndex);
    
    newColumn.innerHTML = `
        <div class="col-md-5">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" name="comparative_columns[${comparativeColumnIndex}][start_date]" required>
        </div>
        <div class="col-md-5">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" name="comparative_columns[${comparativeColumnIndex}][end_date]" required>
        </div>
        <div class="col-md-1">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeComparativeColumn(this)">
                <i class="bx bx-trash me-1"></i> Remove
            </button>
        </div>
    `;
    
    container.appendChild(newColumn);
    comparativeColumnIndex++;
}

function removeComparativeColumn(button) {
    const columnDiv = button.closest('.comparative-column');
    columnDiv.remove();
}

function generateReport() {
    const form = document.getElementById('trialBalanceForm');
    // Remove any export_type parameter that might be present
    const formData = new FormData(form);
    formData.delete('export_type');
    
    // Submit the form without any loading message
    form.submit();
}

function exportReport(type) {
    // Only show loading for actual export operations
    if (type !== 'pdf' && type !== 'excel') {
        return;
    }
    
    const form = document.getElementById('trialBalanceForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    const url = '{{ route("accounting.reports.trial-balance.export") }}?' + new URLSearchParams(formData);
    
    // Show loading state only for exports
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