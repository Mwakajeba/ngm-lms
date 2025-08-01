@extends('layouts.main')

@section('title', 'Balance Sheet Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
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

                                <!-- Comparative Period -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="comparative_years" class="form-label">Comparative Period (Years)</label>
                                    <select class="form-select" id="comparative_years" name="comparative_years">
                                        <option value="0" {{ $comparativeYears == 0 ? 'selected' : '' }}>No Comparison</option>
                                        <option value="1" {{ $comparativeYears == 1 ? 'selected' : '' }}>1 Year</option>
                                        <option value="2" {{ $comparativeYears == 2 ? 'selected' : '' }}>2 Years</option>
                                        <option value="3" {{ $comparativeYears == 3 ? 'selected' : '' }}>3 Years</option>
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
                                <h6 class="mb-3">Balance Sheet as of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</h6>
                                
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
                                                        @for($i = 1; $i <= $comparativeYears; $i++)
                                                            <th class="text-end">{{ $i }} Year{{ $i > 1 ? 's' : '' }} Ago</th>
                                                        @endfor
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
                                                            @for($i = 1; $i <= $comparativeYears; $i++)
                                                                @php
                                                                    $comparativeAsset = $balanceSheetData['comparative'][$i]->first(function($item) use ($asset) {
                                                                        return $levelOfDetail === 'detailed' 
                                                                            ? $item->account_id == $asset->account_id
                                                                            : $item->group_id == $asset->group_id;
                                                                    });
                                                                    $comparativeAmount = $comparativeAsset ? ($comparativeAsset->debit_total - $comparativeAsset->credit_total) : 0;
                                                                @endphp
                                                                <td class="text-end">
                                                                    <span class="text-muted">
                                                                        {{ number_format($comparativeAmount, 2) }}
                                                                    </span>
                                                                </td>
                                                            @endfor
                                                        </tr>
                                                    @endforeach
                                                    <tr class="table-success">
                                                        <td><strong>TOTAL ASSETS</strong></td>
                                                        <td class="text-end">
                                                            <strong>{{ number_format($totalAssets, 2) }}</strong>
                                                        </td>
                                                        @for($i = 1; $i <= $comparativeYears; $i++)
                                                            <td class="text-end">
                                                                <strong class="text-muted">
                                                                    {{ number_format($balanceSheetData['comparative'][$i]->filter(function($item) {
                                                                        return in_array(strtolower($item->class_name), ['assets', 'asset']);
                                                                    })->sum(function($item) {
                                                                        return $item->debit_total - $item->credit_total;
                                                                    }), 2) }}
                                                                </strong>
                                                            </td>
                                                        @endfor
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
                                                        @for($i = 1; $i <= $comparativeYears; $i++)
                                                            <th class="text-end">{{ $i }} Year{{ $i > 1 ? 's' : '' }} Ago</th>
                                                        @endfor
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
                                                            @for($i = 1; $i <= $comparativeYears; $i++)
                                                                @php
                                                                    $comparativeLiability = $balanceSheetData['comparative'][$i]->first(function($item) use ($liability) {
                                                                        return $levelOfDetail === 'detailed' 
                                                                            ? $item->account_id == $liability->account_id
                                                                            : $item->group_id == $liability->group_id;
                                                                    });
                                                                    $comparativeAmount = $comparativeLiability ? ($comparativeLiability->credit_total - $comparativeLiability->debit_total) : 0;
                                                                @endphp
                                                                <td class="text-end">
                                                                    <span class="text-muted">
                                                                        {{ number_format($comparativeAmount, 2) }}
                                                                    </span>
                                                                </td>
                                                            @endfor
                                                        </tr>
                                                    @endforeach
                                                    <tr class="table-warning">
                                                        <td><strong>TOTAL LIABILITIES</strong></td>
                                                        <td class="text-end">
                                                            <strong>{{ number_format($totalLiabilities, 2) }}</strong>
                                                        </td>
                                                        @for($i = 1; $i <= $comparativeYears; $i++)
                                                            <td class="text-end">
                                                                <strong class="text-muted">
                                                                    {{ number_format($balanceSheetData['comparative'][$i]->filter(function($item) {
                                                                        return in_array(strtolower($item->class_name), ['liabilities', 'liability']);
                                                                    })->sum(function($item) {
                                                                        return $item->credit_total - $item->debit_total;
                                                                    }), 2) }}
                                                                </strong>
                                                            </td>
                                                        @endfor
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
                                                            @for($i = 1; $i <= $comparativeYears; $i++)
                                                                <th class="text-end">{{ $i }} Year{{ $i > 1 ? 's' : '' }} Ago</th>
                                                            @endfor
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($balanceSheetData['current']['equity'] as $item)
                                                            @php
                                                                $currentBalance = $item->credit_total - $item->debit_total;
                                                                $comparativeData = collect($balanceSheetData['comparative'][1] ?? [])->first(function($comp) use ($item) {
                                                                    return $levelOfDetail === 'detailed' 
                                                                        ? $comp->account_id == $item->account_id
                                                                        : $comp->group_id == $item->group_id;
                                                                });
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $levelOfDetail === 'detailed' ? $item->account_name : $item->group_name }}</td>
                                                                @if($levelOfDetail === 'detailed')
                                                                    <td>{{ $item->account_code }}</td>
                                                                @endif
                                                                <td class="text-end">{{ number_format($currentBalance, 2) }}</td>
                                                                @for($i = 1; $i <= $comparativeYears; $i++)
                                                                    @php
                                                                        $compData = collect($balanceSheetData['comparative'][$i] ?? [])->first(function($comp) use ($item) {
                                                                            return $levelOfDetail === 'detailed' 
                                                                                ? $comp->account_id == $item->account_id
                                                                                : $comp->group_id == $item->group_id;
                                                                        });
                                                                        $compBalance = $compData ? ($compData->credit_total - $compData->debit_total) : 0;
                                                                    @endphp
                                                                    <td class="text-end">{{ number_format($compBalance, 2) }}</td>
                                                                @endfor
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
                                                            @for($i = 1; $i <= $comparativeYears; $i++)
                                                                @php
                                                                    // Calculate comparative P&L
                                                                    $compIncome = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                                                                        return in_array(strtolower($item->class_name), ['income', 'revenue']);
                                                                    })->sum(function($item) {
                                                                        return $item->credit_total - $item->debit_total;
                                                                    });
                                                                    
                                                                    $compExpenses = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                                                                        return in_array(strtolower($item->class_name), ['expenses', 'expense']);
                                                                    })->sum(function($item) {
                                                                        return $item->debit_total - $item->credit_total;
                                                                    });
                                                                    
                                                                    $compPnL = $compIncome - $compExpenses;
                                                                @endphp
                                                                <td class="text-end">
                                                                    <strong>{{ number_format($compPnL, 2) }}</strong>
                                                                </td>
                                                            @endfor
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
                                                            @for($i = 1; $i <= $comparativeYears; $i++)
                                                                @php
                                                                    $compEquity = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                                                                        return in_array(strtolower($item->class_name), ['equity', 'capital']);
                                                                    })->sum(function($item) {
                                                                        return $item->credit_total - $item->debit_total;
                                                                    });
                                                                    
                                                                    $compIncome = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                                                                        return in_array(strtolower($item->class_name), ['income', 'revenue']);
                                                                    })->sum(function($item) {
                                                                        return $item->credit_total - $item->debit_total;
                                                                    });
                                                                    
                                                                    $compExpenses = collect($balanceSheetData['comparative'][$i] ?? [])->filter(function($item) {
                                                                        return in_array(strtolower($item->class_name), ['expenses', 'expense']);
                                                                    })->sum(function($item) {
                                                                        return $item->debit_total - $item->credit_total;
                                                                    });
                                                                    
                                                                    $compTotalEquity = $compEquity + ($compIncome - $compExpenses);
                                                                @endphp
                                                                <td class="text-end">
                                                                    <strong>{{ number_format($compTotalEquity, 2) }}</strong>
                                                                </td>
                                                            @endfor
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
                                @endphp
                                <div class="alert alert-{{ ($totalAssets == $rightSide) ? 'success' : 'danger' }}">
                                    <div class="d-flex align-items-center">
                                        <i class="bx {{ ($totalAssets == $rightSide) ? 'bx-check-circle' : 'bx-error' }} me-2"></i>
                                        <div>
                                            <strong>Balance Check:</strong>
                                            Assets ({{ number_format($totalAssets, 2) }}) = 
                                            Liabilities ({{ number_format($totalLiabilities, 2) }}) + 
                                            Equity ({{ number_format($totalEquity, 2) }})
                                            <br><small>Where Equity includes P&L ({{ number_format($totalPnL, 2) }})</small>
                                            = {{ number_format($rightSide, 2) }}
                                            @if($totalAssets == $rightSide)
                                                <br><small>✅ Balance sheet is balanced</small>
                                            @else
                                                <br><small>⚠️ Balance sheet is not balanced</small>
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
function generateReport() {
    document.getElementById('balanceSheetForm').submit();
}

function exportReport(type) {
    // Get form data
    const form = document.getElementById('balanceSheetForm');
    const formData = new FormData(form);
    
    // Create export URL with all parameters
    const params = new URLSearchParams(formData);
    params.append('export_type', type);
    
    // Use the export route
    const exportUrl = '{{ route("accounting.reports.balance-sheet.export") }}?' + params.toString();
    
    // Open in new window for download
    window.open(exportUrl, '_blank');
}
</script>
@endsection 