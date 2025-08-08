@extends('layouts.main')

@section('title', __('app.dashboard'))

@php
use Vinkla\Hashids\Facades\Hashids;
@endphp

<style>
    .financial-section {
        margin-bottom: 20px;
    }

    .section-header {
        border-radius: 8px 8px 0 0 !important;
    }

    .section-content {
        border-radius: 0 0 8px 8px !important;
        border-top: none !important;
    }

    .account-row:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }

    .account-row a:hover {
        color: #007bff !important;
        text-decoration: underline !important;
    }

    .table-sm td {
        padding: 0.5rem;
        vertical-align: middle;
    }

    .section-title {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }



    @media print {

        .btn,
        .overlay,
        .back-to-top,
        footer {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .section-header {
            background: #333 !important;
            color: white !important;
        }
    }
</style>

@section('content')
@can('view dashboard')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Welcome Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-home me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">Welcome back, {{ auth()->user()->name }}!</h5>
                                </div>
                                <p class="mb-0 text-muted">Here's what's happening with your financial data today</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('accounting.journals.create') }}" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i> New Journal
                                    </a>
                                    <a href="{{ route('accounting.payment-vouchers.create') }}" class="btn btn-success">
                                        <i class="bx bx-money me-1"></i> New Payment
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row row-cols-1 row-cols-lg-4">
            @can('view charges')
            <div class="col">
                <a href="{{ route('customers.penalty') }}" class="text-decoration-none">
                    <div class="card radius-10">
                        <div class="card-body position-relative">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-muted">Total Penalty</p>
                                    <h4 class="font-weight-bold text-dark">
                                        TZS {{ number_format($penaltyBalance, 2) }}
                                    </h4>
                                    <p class="text-success mb-0 font-13">Penalty balance</p>
                                </div>
                                <div class="widgets-icons bg-gradient-cosmic text-white">
                                    <i class='bx bx-error'></i>
                                </div>
                            </div>
                            <span class="stretched-link"></span>
                        </div>
                    </div>
                </a>
            </div>
            @endcan


            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Journals</p>
                                <h4 class="font-weight-bold">{{ $recentJournals->count() > 0 ? $recentJournals->count() : 0 }}</h4>
                                <p class="text-success mb-0 font-13">This month</p>
                            </div>
                            <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-book-open'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Payments</p>
                                <h4 class="font-weight-bold">{{ $recentPayments->count() > 0 ? $recentPayments->count() : 0 }}</h4>
                                <p class="text-secondary mb-0 font-13">This month</p>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Receipts</p>
                                <h4 class="font-weight-bold">{{ $recentReceipts->count() > 0 ? $recentReceipts->count() : 0 }}</h4>
                                <p class="text-secondary mb-0 font-13">This month</p>
                            </div>
                            <div class="widgets-icons bg-gradient-lush text-white"><i class='bx bx-receipt'></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Bank Reconciliations</p>
                                <h4 class="font-weight-bold">{{ $bankReconciliationStats->total ?? 0 }}</h4>
                                <p class="text-secondary mb-0 font-13">Active reconciliations</p>
                            </div>
                            <div class="widgets-icons bg-gradient-kyoto text-white"><i class='bx bx-bank'></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->



        <!-- Charts Row -->
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="card radius-10">
                    <div class="card-body">
                        <div id="chart1"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card radius-10">
                    <div class="card-body">
                        <div id="chart2"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->

        <!-- Balance Sheet Overview -->
        <div class="row">
            <div class="col-12 col-lg-8 d-lg-flex align-items-lg-stretch">
                <div class="card radius-10 w-100">
                    <div class="card-header border-bottom-0 bg-transparent">
                        <div class="d-lg-flex align-items-center">
                            <div class="">
                                <h5 class="mb-1">Balance Sheet Overview</h5>
                                <p class="text-secondary mb-2 mb-lg-0 font-14">Financial position by account class</p>
                            </div>
                            <div class="ms-lg-auto">
                                <div class="btn-group-round">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-white">Assets</button>
                                        <button type="button" class="btn btn-white">Liabilities</button>
                                        <button type="button" class="btn btn-white">Equity</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chart3"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4 d-lg-flex align-items-lg-stretch">
                <div class="card radius-10 w-100">
                    <div class="card-header bg-transparent">Account Class Balances</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Balance</th>
                                        <th>Accounts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($balanceSheetData as $item)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $item['class_code'] }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $item['class_name'] }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $item['balance'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                TZS {{ number_format(abs($item['balance']), 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $item['account_count'] }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No account data available</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->

        <!-- Recent Activities -->
        <div class="row row-cols-1 row-cols-lg-3">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-book-open me-2"></i>Recent Journals</h6>
                    </div>
                    <div class="card-body">
                        @forelse($recentJournals as $journal)
                        <div class="d-flex align-items-center mb-3">
                            <div class="widgets-icons bg-light-primary text-primary me-3">
                                <i class="bx bx-book"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $journal->reference }}</h6>
                                <p class="mb-0 text-muted">{{ Str::limit($journal->description, 30) }}</p>
                                <small class="text-muted">{{ $journal->date ? $journal->date->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent journals</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-money me-2"></i>Recent Payments</h6>
                    </div>
                    <div class="card-body">
                        @forelse($recentPayments as $payment)
                        <div class="d-flex align-items-center mb-3">
                            <div class="widgets-icons bg-light-success text-success me-3">
                                <i class="bx bx-money"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $payment->reference }}</h6>
                                <p class="mb-0 text-muted">{{ Str::limit($payment->description, 30) }}</p>
                                <small class="text-muted">{{ $payment->date ? $payment->date->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent payments</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-receipt me-2"></i>Recent Receipts</h6>
                    </div>
                    <div class="card-body">
                        @forelse($recentReceipts as $receipt)
                        <div class="d-flex align-items-center mb-3">
                            <div class="widgets-icons bg-light-success text-success me-3">
                                <i class="bx bx-receipt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $receipt->reference }}</h6>
                                <p class="mb-0 text-muted">{{ $receipt->description ?? 'N/A' }}</p>
                                <small class="text-muted">{{ $receipt->date ? $receipt->date->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent receipts</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Report Summary -->
        @can('view FINANCIAL REPORT SUMMARY')
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-0 text-dark"><i class="bx bx-bar-chart me-2"></i>FINANCIAL REPORT SUMMARY</h5>
                                <small class="text-muted">Comprehensive financial overview as of {{ date('d-m-Y') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Balance Sheet Section -->
                            <div class="col-md-6">
                                <div class="financial-section">
                                    <div class="section-header bg-light p-3 rounded-top">
                                        <h4 class="mb-0 text-dark"><i class="bx bx-balance me-2"></i>BALANCE SHEET</h4>
                                        <small class="text-muted">As of {{ date('d-m-Y') }} vs {{ $previousYearData['year'] }}</small>
                                    </div>

                                    <!-- Assets Section -->
                                    <div class="section-content border rounded-bottom">
                                        <div class="section-title bg-light p-2 border-bottom">
                                            <h6 class="mb-0 text-success"><i class="bx bx-trending-up me-1"></i>ASSETS</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumAsset = 0; $sumAssetPrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsAssets'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum(fn($account) => $account['sum'] ?? 0); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr class="table-light">
                                                        <td colspan="4" class="fw-bold text-primary">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountAsset)
                                                    @if($chartAccountAsset['sum'] != 0)
                                                    @php 
                                                        $sumAsset += $chartAccountAsset['sum'] ?? 0;
                                                        $prevYearAccount = collect($previousYearData['chartAccountsAssets'][$groupName] ?? [])->firstWhere('account_id', $chartAccountAsset['account_id']);
                                                        $prevYearAmount = $prevYearAccount['sum'] ?? 0;
                                                        $sumAssetPrev += $prevYearAmount;
                                                        $change = ($chartAccountAsset['sum'] ?? 0) - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountAsset['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-success"></i>
                                                                {{ $chartAccountAsset['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountAsset['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-success">
                                                                {{ number_format($chartAccountAsset['sum'] ?? 0,2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-muted">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="badge {{ $change >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="table-success fw-bold">
                                                        <td>TOTAL ASSETS</td>
                                                        <td class="text-end">{{ number_format($sumAsset,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumAssetPrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $assetChange = $sumAsset - $sumAssetPrev; @endphp
                                                            <span class="badge {{ $assetChange >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $assetChange >= 0 ? '+' : '' }}{{ number_format($assetChange,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Equity Section -->
                                        <div class="section-title bg-light p-2 border-bottom mt-3">
                                            <h6 class="mb-0 text-info"><i class="bx bx-user me-1"></i>EQUITY</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumEquity = 0; $sumEquityPrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsEquitys'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum(fn($account) => $account['sum'] ?? 0); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr class="table-light">
                                                        <td colspan="4" class="fw-bold text-primary">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountEquity)
                                                    @if($chartAccountEquity['sum'] != 0)
                                                    @php 
                                                        $sumEquity += abs($chartAccountEquity['sum'] ?? 0);
                                                        $prevYearAccount = collect($previousYearData['chartAccountsEquitys'][$groupName] ?? [])->firstWhere('account_id', $chartAccountEquity['account_id']);
                                                        $prevYearAmount = abs($prevYearAccount['sum'] ?? 0);
                                                        $sumEquityPrev += $prevYearAmount;
                                                        $change = abs($chartAccountEquity['sum'] ?? 0) - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountEquity['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-info"></i>
                                                                {{ $chartAccountEquity['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountEquity['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-info">
                                                                {{ number_format(abs($chartAccountEquity['sum'] ?? 0),2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-muted">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="badge {{ $change >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="table-info">
                                                        <td>Profit And Loss</td>
                                                        <td class="text-end fw-bold">{{ number_format($financialReportData['profitLoss'],2) }}</td>
                                                        <td class="text-end text-muted">{{ number_format($previousYearData['profitLoss'],2) }}</td>
                                                        <td class="text-end">
                                                            @php $profitChange = $financialReportData['profitLoss'] - $previousYearData['profitLoss']; @endphp
                                                            <span class="badge {{ $profitChange >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $profitChange >= 0 ? '+' : '' }}{{ number_format($profitChange,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr class="table-info fw-bold">
                                                        <td>TOTAL EQUITY</td>
                                                        <td class="text-end">{{ number_format($sumEquity + $financialReportData['profitLoss'],2) }}</td>
                                                        <td class="text-end">{{ number_format($sumEquityPrev + $previousYearData['profitLoss'],2) }}</td>
                                                        <td class="text-end">
                                                            @php $equityChange = ($sumEquity + $financialReportData['profitLoss']) - ($sumEquityPrev + $previousYearData['profitLoss']); @endphp
                                                            <span class="badge {{ $equityChange >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $equityChange >= 0 ? '+' : '' }}{{ number_format($equityChange,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Liabilities Section -->
                                        <div class="section-title bg-light p-2 border-bottom mt-3">
                                            <h6 class="mb-0 text-warning"><i class="bx bx-trending-down me-1"></i>LIABILITIES</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumLiability = 0; $sumLiabilityPrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsLiabilities'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum(fn($account) => $account['sum'] ?? 0); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr class="table-light">
                                                        <td colspan="4" class="fw-bold text-primary">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountLiability)
                                                    @if($chartAccountLiability['sum'] != 0)
                                                    @php 
                                                        $sumLiability += abs($chartAccountLiability['sum'] ?? 0);
                                                        $prevYearAccount = collect($previousYearData['chartAccountsLiabilities'][$groupName] ?? [])->firstWhere('account_id', $chartAccountLiability['account_id']);
                                                        $prevYearAmount = abs($prevYearAccount['sum'] ?? 0);
                                                        $sumLiabilityPrev += $prevYearAmount;
                                                        $change = abs($chartAccountLiability['sum'] ?? 0) - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountLiability['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-warning"></i>
                                                                {{ $chartAccountLiability['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountLiability['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-warning">
                                                                {{ number_format(abs($chartAccountLiability['sum'] ?? 0),2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-muted">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="badge {{ $change >= 0 ? 'bg-warning' : 'bg-success' }}">
                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="table-warning fw-bold">
                                                        <td>TOTAL LIABILITIES</td>
                                                        <td class="text-end">{{ number_format($sumLiability,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumLiabilityPrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $liabilityChange = $sumLiability - $sumLiabilityPrev; @endphp
                                                            <span class="badge {{ $liabilityChange >= 0 ? 'bg-warning' : 'bg-success' }}">
                                                                {{ $liabilityChange >= 0 ? '+' : '' }}{{ number_format($liabilityChange,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr class="table-dark fw-bold">
                                                        <td>TOTAL EQUITY & LIABILITY</td>
                                                        <td class="text-end">{{ number_format($sumLiability + $sumEquity + $financialReportData['profitLoss'],2) }}</td>
                                                        <td class="text-end">{{ number_format($sumLiabilityPrev + $sumEquityPrev + $previousYearData['profitLoss'],2) }}</td>
                                                        <td class="text-end">
                                                            @php $totalChange = ($sumLiability + $sumEquity + $financialReportData['profitLoss']) - ($sumLiabilityPrev + $sumEquityPrev + $previousYearData['profitLoss']); @endphp
                                                            <span class="badge {{ $totalChange >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $totalChange >= 0 ? '+' : '' }}{{ number_format($totalChange,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profit & Loss Section -->
                            <div class="col-md-6">
                                <div class="financial-section">
                                    <div class="section-header bg-light p-3 rounded-top">
                                        <h4 class="mb-0 text-dark"><i class="bx bx-line-chart me-2"></i>PROFIT & LOSS STATEMENT</h4>
                                        <small class="text-muted">From 01-01-{{date('Y')}} to {{ date('d-m-Y') }} vs {{ $previousYearData['year'] }}</small>
                                    </div>

                                    <div class="section-content border rounded-bottom">
                                        <!-- Revenue Section -->
                                        <div class="section-title bg-light p-2 border-bottom">
                                            <h6 class="mb-0 text-success"><i class="bx bx-trending-up me-1"></i>INCOME</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumRevenue = 0; $sumRevenuePrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsRevenues'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum('sum'); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr class="table-light">
                                                        <td colspan="4" class="fw-bold text-primary">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountRevenue)
                                                    @if($chartAccountRevenue['sum'] != 0)
                                                    @php 
                                                        $sumRevenue += $chartAccountRevenue['sum'];
                                                        $prevYearAccount = collect($previousYearData['chartAccountsRevenues'][$groupName] ?? [])->firstWhere('account_id', $chartAccountRevenue['account_id']);
                                                        $prevYearAmount = $prevYearAccount['sum'] ?? 0;
                                                        $sumRevenuePrev += $prevYearAmount;
                                                        $change = $chartAccountRevenue['sum'] - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountRevenue['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-success"></i>
                                                                {{ $chartAccountRevenue['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountRevenue['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-success">
                                                                {{ number_format($chartAccountRevenue['sum'],2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-muted">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="badge {{ $change >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="table-success fw-bold">
                                                        <td>TOTAL INCOME</td>
                                                        <td class="text-end">{{ number_format($sumRevenue,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumRevenuePrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $revenueChange = $sumRevenue - $sumRevenuePrev; @endphp
                                                            <span class="badge {{ $revenueChange >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $revenueChange >= 0 ? '+' : '' }}{{ number_format($revenueChange,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Expenses Section -->
                                        <div class="section-title bg-light p-2 border-bottom mt-3">
                                            <h6 class="mb-0 text-danger"><i class="bx bx-trending-down me-1"></i>EXPENSES</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Current Year</th>
                                                        <th class="text-end">Previous Year</th>
                                                        <th class="text-end">Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumExpense = 0; $sumExpensePrev = 0; @endphp
                                                    @foreach($financialReportData['chartAccountsExpense'] as $groupName => $accounts)
                                                    @php $groupTotal = collect($accounts)->sum('sum'); @endphp
                                                    @if($groupTotal != 0)
                                                    <tr class="table-light">
                                                        <td colspan="4" class="fw-bold text-primary">{{ $groupName }}</td>
                                                    </tr>
                                                    @foreach($accounts as $chartAccountExpense)
                                                    @if($chartAccountExpense['sum'] != 0)
                                                    @php 
                                                        $sumExpense += abs($chartAccountExpense['sum']);
                                                        $prevYearAccount = collect($previousYearData['chartAccountsExpense'][$groupName] ?? [])->firstWhere('account_id', $chartAccountExpense['account_id']);
                                                        $prevYearAmount = abs($prevYearAccount['sum'] ?? 0);
                                                        $sumExpensePrev += $prevYearAmount;
                                                        $change = abs($chartAccountExpense['sum']) - $prevYearAmount;
                                                    @endphp
                                                    <tr class="account-row">
                                                        <td>
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountExpense['account_id'])) }}"
                                                                class="text-decoration-none text-dark fw-medium">
                                                                <i class="bx bx-chevron-right me-1 text-danger"></i>
                                                                {{ $chartAccountExpense['account'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($chartAccountExpense['account_id'])) }}"
                                                                class="text-decoration-none fw-bold text-danger">
                                                                {{ number_format(abs($chartAccountExpense['sum']),2) }}
                                                            </a>
                                                        </td>
                                                        <td class="text-end text-muted">
                                                            {{ number_format($prevYearAmount,2) }}
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="badge {{ $change >= 0 ? 'bg-danger' : 'bg-success' }}">
                                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @endforeach
                                                    @endif
                                                    @endforeach
                                                    <tr class="table-danger fw-bold">
                                                        <td>TOTAL EXPENSES</td>
                                                        <td class="text-end">{{ number_format($sumExpense,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumExpensePrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $expenseChange = $sumExpense - $sumExpensePrev; @endphp
                                                            <span class="badge {{ $expenseChange >= 0 ? 'bg-danger' : 'bg-success' }}">
                                                                {{ $expenseChange >= 0 ? '+' : '' }}{{ number_format($expenseChange,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr class="table-{{ ($sumRevenue - $sumExpense) >= 0 ? 'success' : 'danger' }} fw-bold fs-5">
                                                        <td>NET PROFIT/LOSS</td>
                                                        <td class="text-end">{{ number_format($sumRevenue - $sumExpense,2) }}</td>
                                                        <td class="text-end">{{ number_format($sumRevenuePrev - $sumExpensePrev,2) }}</td>
                                                        <td class="text-end">
                                                            @php $netProfitChange = ($sumRevenue - $sumExpense) - ($sumRevenuePrev - $sumExpensePrev); @endphp
                                                            <span class="badge {{ $netProfitChange >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $netProfitChange >= 0 ? '+' : '' }}{{ number_format($netProfitChange,2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan

    </div>
</div>
@endcan
<!--end page wrapper -->
<!--start overlay-->
<div class="overlay toggle-icon"></div>
<!--end overlay-->
<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<!--End Back To Top Button-->
<footer class="page-footer">
    <p class="mb-0">Copyright © 2021. All right reserved.</p>
</footer>
@endsection