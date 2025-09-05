@extends('layouts.main')

@section('title', 'Balance Sheet Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Balance Sheet Report', 'url' => '#', 'icon' => 'bx bx-bar-chart']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-bar-chart me-2"></i>Balance Sheet Report</h5>
                                <small class="text-muted">Generate comprehensive balance sheet reports with filters</small>
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
                        <form id="balanceSheetForm" method="GET" action="{{ route('accounting.reports.balance-sheet') }}">
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

                            <!-- Dynamic Comparative Columns -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Comparative Columns</h6>
                                        <button type="button" class="btn btn-success btn-sm" onclick="addComparativeColumn()">
                                            <i class="bx bx-plus me-1"></i> Add Comparative Column
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="comparativeColumnsContainer">
                                @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                    @foreach($comparativeColumns as $index => $column)
                                        <div class="row mb-2 comparative-column" data-index="{{ $index }}">
                                            <div class="col-md-4">
                                                <label class="form-label">Date</label>
                                                <input type="date" class="form-control" name="comparative_columns[{{ $index }}][date]" value="{{ $column['date'] }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Column Name (Optional)</label>
                                                <input type="text" class="form-control" name="comparative_columns[{{ $index }}][name]" value="{{ $column['name'] }}" placeholder="e.g., Previous Year">
                                            </div>
                                            <div class="col-md-2">
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
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search me-1"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('accounting.reports.balance-sheet') }}" class="btn btn-outline-secondary ms-2">
                                        <i class="bx bx-reset me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <hr>

                        <!-- Report Results -->
                        @if(isset($balanceSheetData))
                        <div class="row">
                            <div class="col-12">
                                <!-- Report Header -->
                                <div class="text-center mb-4">
                                    <h4 class="mb-1"><strong>{{ $user->company->name ?? 'Company Name' }}</strong></h4>
                                    <h6 class="text-muted mb-2">BALANCE SHEET</h6>
                                    <p class="mb-1">As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</p>
                                    @if($branchId && $branchId != 'all')
                                        <p class="mb-1 text-muted">Branch: {{ $branches->where('id', $branchId)->first()->name ?? 'N/A' }}</p>
                                    @endif
                                    <p class="mb-0 text-muted small">
                                        {{ ucfirst($reportingType) }} Basis | 
                                        {{ ucfirst($levelOfDetail) }} Level |
                                        Generated on {{ now()->format('F d, Y \a\t g:i A') }}
                                    </p>
                                </div>
                                
                                <!-- Assets Section -->
                                <div class="card border-success mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-trending-up me-2"></i>ASSETS</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Account/Group</th>
                                                        <th class="text-end">Current Period</th>
                                                        @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                            @foreach($comparativeColumns as $column)
                                                                <th class="text-end">{{ $column['name'] ?: \Carbon\Carbon::parse($column['date'])->format('M d, Y') }}</th>
                                                            @endforeach
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $totalAssets = 0; @endphp
                                                    @foreach($balanceSheetData['current']['assets'] as $asset)
                                                        @php 
                                                            $currentAmount = $asset->debit_total - $asset->credit_total;
                                                            $totalAssets += $currentAmount;
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                @if($levelOfDetail === 'detailed')
                                                                    <strong>{{ $asset->account_name }}</strong>
                                                                    <br><small class="text-muted">{{ $asset->account_code }}</small>
                                                                @else
                                                                    <strong>{{ $asset->group_name }}</strong>
                                                                @endif
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="fw-bold text-success">
                                                                    {{ number_format($currentAmount, 2) }}
                                                                </span>
                                                            </td>
                                                            @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                                @foreach($comparativeColumns as $column)
                                                                    @php
                                                                        $comparativeData = $balanceSheetData['comparative'][$column['name']] ?? [];
                                                                        $comparativeAsset = collect($comparativeData['assets'] ?? [])->first(function($item) use ($asset, $levelOfDetail) {
                                                                            if (!$item) return false;
                                                                            return $levelOfDetail === 'detailed' 
                                                                                ? (isset($item->account_id) && isset($asset->account_id) && $item->account_id == $asset->account_id)
                                                                                : (isset($item->group_id) && isset($asset->group_id) && $item->group_id == $asset->group_id);
                                                                        });
                                                                        $comparativeAmount = $comparativeAsset ? ($comparativeAsset->debit_total - $comparativeAsset->credit_total) : 0;
                                                                    @endphp
                                                                    <td class="text-end">
                                                                        <span class="text-muted">
                                                                            {{ number_format($comparativeAmount, 2) }}
                                                                        </span>
                                                                    </td>
                                                                @endforeach
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                    <tr class="table-success">
                                                        <td><strong>TOTAL ASSETS</strong></td>
                                                        <td class="text-end">
                                                            <strong>{{ number_format($totalAssets, 2) }}</strong>
                                                        </td>
                                                        @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                            @foreach($comparativeColumns as $column)
                                                                @php
                                                                    $comparativeData = $balanceSheetData['comparative'][$column['name']] ?? [];
                                                                    $comparativeTotal = collect($comparativeData['assets'] ?? [])->sum(function($item) {
                                                                        return $item->debit_total - $item->credit_total;
                                                                    });
                                                                @endphp
                                                                <td class="text-end">
                                                                    <strong class="text-muted">
                                                                        {{ number_format($comparativeTotal, 2) }}
                                                                    </strong>
                                                                </td>
                                                            @endforeach
                                                        @endif
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Liabilities Section -->
                                <div class="card border-warning mb-4">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="bx bx-trending-down me-2"></i>LIABILITIES</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Account/Group</th>
                                                        <th class="text-end">Current Period</th>
                                                        @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                            @foreach($comparativeColumns as $column)
                                                                <th class="text-end">{{ $column['name'] ?: \Carbon\Carbon::parse($column['date'])->format('M d, Y') }}</th>
                                                            @endforeach
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $totalLiabilities = 0; @endphp
                                                    @foreach($balanceSheetData['current']['liabilities'] as $liability)
                                                        @php 
                                                            $currentAmount = $liability->credit_total - $liability->debit_total;
                                                            $totalLiabilities += $currentAmount;
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                @if($levelOfDetail === 'detailed')
                                                                    <strong>{{ $liability->account_name }}</strong>
                                                                    <br><small class="text-muted">{{ $liability->account_code }}</small>
                                                                @else
                                                                    <strong>{{ $liability->group_name }}</strong>
                                                                @endif
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="fw-bold text-warning">
                                                                    {{ number_format($currentAmount, 2) }}
                                                                </span>
                                                            </td>
                                                            @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                                @foreach($comparativeColumns as $column)
                                                                    @php
                                                                        $comparativeData = $balanceSheetData['comparative'][$column['name']] ?? [];
                                                                        $comparativeLiability = collect($comparativeData)->first(function($item) use ($liability, $levelOfDetail) {
                                                                            if (!$item) return false;
                                                                            return $levelOfDetail === 'detailed' 
                                                                                ? (isset($item->account_id) && isset($liability->account_id) && $item->account_id == $liability->account_id)
                                                                                : (isset($item->group_id) && isset($liability->group_id) && $item->group_id == $liability->group_id);
                                                                        });
                                                                        $comparativeAmount = $comparativeLiability ? ($comparativeLiability->credit_total - $comparativeLiability->debit_total) : 0;
                                                                    @endphp
                                                                    <td class="text-end">
                                                                        <span class="text-muted">
                                                                            {{ number_format($comparativeAmount, 2) }}
                                                                        </span>
                                                                    </td>
                                                                @endforeach
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                    <tr class="table-warning">
                                                        <td><strong>TOTAL LIABILITIES</strong></td>
                                                        <td class="text-end">
                                                            <strong>{{ number_format($totalLiabilities, 2) }}</strong>
                                                        </td>
                                                        @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                            @foreach($comparativeColumns as $column)
                                                                @php
                                                                    $comparativeData = $balanceSheetData['comparative'][$column['name']] ?? [];
                                                                    $comparativeTotal = collect($comparativeData['liabilities'] ?? [])->sum(function($item) {
                                                                        return $item->credit_total - $item->debit_total;
                                                                    });
                                                                @endphp
                                                                <td class="text-end">
                                                                    <strong class="text-muted">
                                                                        {{ number_format($comparativeTotal, 2) }}
                                                                    </strong>
                                                                </td>
                                                            @endforeach
                                                        @endif
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Equity Section -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">EQUITY</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($balanceSheetData['current']['equity']->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Account</th>
                                                            @if($levelOfDetail === 'detailed')
                                                                <th>Code</th>
                                                            @endif
                                                            <th class="text-end">Current Period</th>
                                                            @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                                @foreach($comparativeColumns as $column)
                                                                    <th class="text-end">{{ $column['name'] ?: \Carbon\Carbon::parse($column['date'])->format('M d, Y') }}</th>
                                                                @endforeach
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($balanceSheetData['current']['equity'] as $item)
                                                            @php
                                                                $currentBalance = $item->credit_total - $item->debit_total;
                                                                $comparativeData = collect($balanceSheetData['comparative'][1] ?? [])->first(function($comp) use ($item) {
                                                                    return $comp->account_id == $item->account_id;
                                                                });
                                                                $comparativeBalance = $comparativeData ? ($comparativeData->credit_total - $comparativeData->debit_total) : 0;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $levelOfDetail === 'detailed' ? $item->account_name : $item->group_name }}</td>
                                                                @if($levelOfDetail === 'detailed')
                                                                    <td>{{ $item->account_code }}</td>
                                                                @endif
                                                                <td class="text-end">{{ number_format($currentBalance, 2) }}</td>
                                                                @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                                    @foreach($comparativeColumns as $column)
                                                                        @php
                                                                            $compData = collect($balanceSheetData['comparative'][$column['name']] ?? [])->first(function($comp) use ($item, $levelOfDetail) {
                                                                                if (!$comp) return false;
                                                                                return $levelOfDetail === 'detailed' 
                                                                                    ? (isset($comp->account_id) && isset($item->account_id) && $comp->account_id == $item->account_id)
                                                                                    : (isset($comp->group_id) && isset($item->group_id) && $comp->group_id == $item->group_id);
                                                                            });
                                                                            $compBalance = $compData ? ($compData->credit_total - $compData->debit_total) : 0;
                                                                        @endphp
                                                                        <td class="text-end">{{ number_format($compBalance, 2) }}</td>
                                                                    @endforeach
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                        
                                                        <!-- Profit & Loss Section -->
                                                        <tr class="table-info">
                                                            <td><strong>Profit & Loss</strong></td>
                                                            @if($levelOfDetail === 'detailed')
                                                                <td></td>
                                                            @endif
                                                            <td class="text-end">
                                                                <strong>{{ number_format($balanceSheetData['profit_loss'], 2) }}</strong>
                                                            </td>
                                                            @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                                @foreach($comparativeColumns as $column)
                                                                    @php
                                                                        // Calculate comparative P&L - simplified for now
                                                                        $comparativeData = $balanceSheetData['comparative'][$column['name']] ?? [];
                                                                        $compPnL = 0; // Default to 0 for comparative P&L
                                                                    @endphp
                                                                    <td class="text-end">
                                                                        <strong>{{ number_format($compPnL, 2) }}</strong>
                                                                    </td>
                                                                @endforeach
                                                            @endif
                                                        </tr>
                                                        
                                                        <!-- Total Equity -->
                                                        @php
                                                            $totalEquity = $balanceSheetData['current']['equity']->sum(function($item) {
                                                                return $item->credit_total - $item->debit_total;
                                                            }) + $balanceSheetData['profit_loss'];
                                                        @endphp
                                                        <tr class="table-primary">
                                                            <td><strong>Total Equity</strong></td>
                                                            @if($levelOfDetail === 'detailed')
                                                                <td></td>
                                                            @endif
                                                            <td class="text-end">
                                                                <strong>{{ number_format($totalEquity, 2) }}</strong>
                                                            </td>
                                                            @if(isset($comparativeColumns) && count($comparativeColumns) > 0)
                                                                @foreach($comparativeColumns as $column)
                                                                    @php
                                                                        $comparativeData = $balanceSheetData['comparative'][$column['name']] ?? [];
                                                                        $compEquity = collect($comparativeData['equity'] ?? [])->sum(function($item) {
                                                                            return $item->credit_total - $item->debit_total;
                                                                        });
                                                                        
                                                                        $compTotalEquity = $compEquity; // Simplified for now
                                                                    @endphp
                                                                    <td class="text-end">
                                                                        <strong>{{ number_format($compTotalEquity, 2) }}</strong>
                                                                    </td>
                                                                @endforeach
                                                            @endif
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted">No equity accounts found.</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Balance Check -->
                                @php
                                    $totalAssets = $balanceSheetData['current']['assets']->sum(function($item) {
                                        return $item->debit_total - $item->credit_total;
                                    });
                                    $totalLiabilities = $balanceSheetData['current']['liabilities']->sum(function($item) {
                                        return $item->credit_total - $item->debit_total;
                                    });
                                    $baseEquity = $balanceSheetData['current']['equity']->sum(function($item) {
                                        return $item->credit_total - $item->debit_total;
                                    });
                                    
                                    // Always use with P&L logic
                                    $totalPnL = $balanceSheetData['profit_loss'];
                                    $totalEquity = $baseEquity + $totalPnL;
                                    $rightSide = $totalLiabilities + $totalEquity;
                                    
                                    // Use a tolerance to account for rounding/floating differences
                                    $difference = $totalAssets - $rightSide;
                                    $isBalanced = abs($difference) < 0.01; // tolerance of 1 cent
                                @endphp
                                <div class="alert alert-{{ $isBalanced ? 'success' : 'danger' }}">
                                    <div class="d-flex align-items-center">
                                        <i class="bx {{ $isBalanced ? 'bx-check-circle' : 'bx-error' }} me-2"></i>
                                        <div>
                                            <strong>Balance Check:</strong>
                                            Assets ({{ number_format($totalAssets, 2) }}) = 
                                            Liabilities ({{ number_format($totalLiabilities, 2) }}) + 
                                            Equity ({{ number_format($totalEquity, 2) }})
                                            <br>
                                            <small>Where Equity includes P&L ({{ number_format($totalPnL, 2) }})</small>
                                            <br>
                                            <strong>= {{ number_format($rightSide, 2) }}</strong>
                                            @if($isBalanced)
                                                <br><small>✅ Balance sheet is balanced</small>
                                            @else
                                                <br><small>⚠️ Balance sheet is not balanced. Difference: {{ number_format($difference, 2) }}</small>
                                            @endif
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
        <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="comparative_columns[${comparativeColumnIndex}][date]" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Column Name (Optional)</label>
            <input type="text" class="form-control" name="comparative_columns[${comparativeColumnIndex}][name]" placeholder="e.g., Previous Year">
        </div>
        <div class="col-md-2">
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
    document.getElementById('balanceSheetForm').submit();
}

function exportReport(type) {
    const form = document.getElementById('balanceSheetForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    const url = '{{ route("accounting.reports.balance-sheet.export") }}?' + new URLSearchParams(formData);
    
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
    
    // close the loading state after a short delay
    setTimeout(() => {
        Swal.close();
    }, 2000);
}
</script>
@endsection 