<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-date {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .report-details {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .assets-header {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        .liabilities-header {
            background-color: #fff3cd;
            color: #856404;
            font-weight: bold;
        }
        .equity-header {
            background-color: #d1ecf1;
            color: #0c5460;
            font-weight: bold;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .balance-check {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .success {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .danger {
            border-color: #dc3545;
            background-color: #f8d7da;
        }
        .page-break {
            page-break-before: always;
        }
        .logo-wrapper { text-align: center; margin-bottom: 10px; }
        .logo-wrapper img { max-height: 70px; }
    </style>
</head>
<body>
    @php
        $companyModel = isset($company) ? $company : (function_exists('current_company') ? current_company() : null);
        $logoPath = ($companyModel && !empty($companyModel->logo)) ? public_path('storage/' . $companyModel->logo) : null;
    @endphp
    @if($logoPath && file_exists($logoPath))
        <div class="logo-wrapper">
            <img src="{{ $logoPath }}" alt="Company Logo">
        </div>
    @endif
    <!-- Report Header -->
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
        <div class="report-title">BALANCE SHEET</div>
        <div class="report-date">As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</div>
        @if(isset($balanceSheetData['filters']['branch_id']) && $balanceSheetData['filters']['branch_id'] != 'all')
            <div class="report-details">Branch: {{ collect($branches)->where('id', $balanceSheetData['filters']['branch_id'])->first()['name'] ?? 'N/A' }}</div>
        @endif
        <div class="report-details">
            {{ ucfirst($reportingType) }} Basis | 
            {{ ucfirst($balanceSheetData['filters']['level_of_detail']) }} Level |
            Generated on {{ now()->format('F d, Y \\a\\t g:i A') }}
        </div>
    </div>

    <!-- Balance Sheet Summary -->
    <div class="balance-summary">
        <h3 style="text-align: center; background-color: #6F42C1; color: white; padding: 10px; margin: 20px 0;">BALANCE SHEET SUMMARY</h3>
        <table style="margin-bottom: 20px;">
            <tr>
                <td style="width: 50%; text-align: center; padding: 15px; border: 2px solid #28a745;">
                    <h4 style="color: #28a745; margin: 0;">TOTAL ASSETS</h4>
                    <h2 style="color: #28a745; margin: 10px 0;">{{ number_format($balanceSheetData['current']['assets']->sum(function($item) { return $item->debit_total - $item->credit_total; }), 2) }}</h2>
                </td>
                <td style="width: 50%; text-align: center; padding: 15px; border: 2px solid #17a2b8;">
                    <h4 style="color: #17a2b8; margin: 0;">TOTAL LIABILITIES + EQUITY</h4>
                    <h2 style="color: #17a2b8; margin: 10px 0;">{{ number_format(($balanceSheetData['current']['liabilities']->sum(function($item) { return $item->credit_total - $item->debit_total; }) + $balanceSheetData['current']['equity']->sum(function($item) { return $item->credit_total - $item->debit_total; }) + $balanceSheetData['profit_loss']), 2) }}</h2>
                    <small style="color: #666;">
                        Liabilities: {{ number_format($balanceSheetData['current']['liabilities']->sum(function($item) { return $item->credit_total - $item->debit_total; }), 2) }} + 
                        Equity: {{ number_format($balanceSheetData['current']['equity']->sum(function($item) { return $item->credit_total - $item->debit_total; }) + $balanceSheetData['profit_loss'], 2) }}
                    </small>
                </td>
            </tr>
        </table>
        @php
            $totalAssets = $balanceSheetData['current']['assets']->sum(function($item) { return $item->debit_total - $item->credit_total; });
            $totalLiabilities = $balanceSheetData['current']['liabilities']->sum(function($item) { return $item->credit_total - $item->debit_total; });
            $totalEquity = $balanceSheetData['current']['equity']->sum(function($item) { return $item->credit_total - $item->debit_total; }) + $balanceSheetData['profit_loss'];
            $totalLiabilitiesPlusEquity = $totalLiabilities + $totalEquity;
            $difference = $totalAssets - $totalLiabilitiesPlusEquity;
            $isBalanced = abs($difference) < 0.01;
        @endphp
        <div class="balance-check {{ $isBalanced ? 'success' : 'danger' }}" style="text-align: center; margin: 20px 0;">
            <strong>
                @if($isBalanced)
                    Balance Sheet is Balanced
                @else
                    Balance Sheet Difference: {{ number_format($difference, 2) }}
                @endif
            </strong>
        </div>
    </div>

    <!-- Assets Section -->
    <div class="section-header">ASSETS</div>
    <table>
        <thead>
            <tr>
                <th>Account/Group</th>
                <th class="text-center">Current Period</th>
                @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                    @foreach($balanceSheetData['comparative'] as $columnName => $data)
                        <th class="text-center">{{ $columnName }}</th>
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
                        @if($balanceSheetData['filters']['level_of_detail'] === 'detailed')
                            <strong>{{ $asset->account_name }}</strong><br>
                            <small>{{ $asset->account_code }}</small>
                        @else
                            <strong>{{ $asset->group_name }}</strong>
                        @endif
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($currentAmount, 2) }}</strong>
                    </td>
                    @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                        @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                            @php
                                $comparativeAsset = collect($comparativeData['assets'] ?? [])->first(function($item) use ($asset) {
                                    return $balanceSheetData['filters']['level_of_detail'] === 'detailed' 
                                        ? $item->account_id == $asset->account_id
                                        : $item->group_id == $asset->group_id;
                                });
                                $comparativeAmount = $comparativeAsset ? ($comparativeAsset->debit_total - $comparativeAsset->credit_total) : 0;
                            @endphp
                            <td class="text-right">
                                {{ number_format($comparativeAmount, 2) }}
                            </td>
                        @endforeach
                    @endif
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL ASSETS</strong></td>
                <td class="text-right">
                    <strong>{{ number_format($totalAssets, 2) }}</strong>
                </td>
                @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                    @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                        @php
                            $comparativeTotal = collect($comparativeData['assets'] ?? [])->sum(function($item) {
                                return $item->debit_total - $item->credit_total;
                            });
                        @endphp
                        <td class="text-right">
                            <strong>{{ number_format($comparativeTotal, 2) }}</strong>
                        </td>
                    @endforeach
                @endif
            </tr>
        </tbody>
    </table>

    <!-- Liabilities Section -->
    <div class="section-header">LIABILITIES</div>
    <table>
        <thead>
            <tr>
                <th>Account/Group</th>
                <th class="text-center">Current Period</th>
                @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                    @foreach($balanceSheetData['comparative'] as $columnName => $data)
                        <th class="text-center">{{ $columnName }}</th>
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
                        @if($balanceSheetData['filters']['level_of_detail'] === 'detailed')
                            <strong>{{ $liability->account_name }}</strong><br>
                            <small>{{ $liability->account_code }}</small>
                        @else
                            <strong>{{ $liability->group_name }}</strong>
                        @endif
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($currentAmount, 2) }}</strong>
                    </td>
                    @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                        @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                            @php
                                $comparativeLiability = collect($comparativeData['liabilities'] ?? [])->first(function($item) use ($liability) {
                                    return $balanceSheetData['filters']['level_of_detail'] === 'detailed' 
                                        ? $item->account_id == $liability->account_id
                                        : $item->group_id == $liability->group_id;
                                });
                                $comparativeAmount = $comparativeLiability ? ($comparativeLiability->credit_total - $comparativeLiability->debit_total) : 0;
                            @endphp
                            <td class="text-right">
                                {{ number_format($comparativeAmount, 2) }}
                            </td>
                        @endforeach
                    @endif
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL LIABILITIES</strong></td>
                <td class="text-right">
                    <strong>{{ number_format($totalLiabilities, 2) }}</strong>
                </td>
                @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                    @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                        @php
                            $comparativeTotal = collect($comparativeData['liabilities'] ?? [])->sum(function($item) {
                                return $item->credit_total - $item->debit_total;
                            });
                        @endphp
                        <td class="text-right">
                            <strong>{{ number_format($comparativeTotal, 2) }}</strong>
                        </td>
                    @endforeach
                @endif
            </tr>
        </tbody>
    </table>

    <!-- Equity Section -->
    <div class="section">
        <h3>EQUITY</h3>
        @if($balanceSheetData['current']['equity']->count() > 0 || isset($balanceSheetData['profit_loss']))
            <table class="table">
                <thead>
                    <tr>
                        <th>Account</th>
                        @if($balanceSheetData['filters']['level_of_detail'] === 'detailed')
                            <th>Code</th>
                        @endif
                        <th class="text-end">Current Period</th>
                        @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                            @foreach($balanceSheetData['comparative'] as $columnName => $data)
                                <th class="text-end">{{ $columnName }}</th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($balanceSheetData['current']['equity'] as $item)
                        @php
                            $currentBalance = $item->credit_total - $item->debit_total;
                        @endphp
                        <tr>
                            <td>{{ $balanceSheetData['filters']['level_of_detail'] === 'detailed' ? $item->account_name : $item->group_name }}</td>
                            @if($balanceSheetData['filters']['level_of_detail'] === 'detailed')
                                <td>{{ $item->account_code }}</td>
                            @endif
                            <td class="text-end">{{ number_format($currentBalance, 2) }}</td>
                            @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                                @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                                    @php
                                        $compData = collect($comparativeData['equity'] ?? [])->first(function($comp) use ($item) {
                                            return $balanceSheetData['filters']['level_of_detail'] === 'detailed' 
                                                ? $comp->account_id == $item->account_id
                                                : $comp->group_id == $item->group_id;
                                        });
                                        $compBalance = $compData ? ($compData->credit_total - $compData->debit_total) : 0;
                                    @endphp
                                    <td class="text-end">{{ number_format($compBalance, 2) }}</td>
                                @endforeach
                            @endif
                        </tr>
                    @endforeach
                    
                    <!-- Profit & Loss Section -->
                    <tr class="pnl-row">
                        <td><strong>Profit & Loss</strong></td>
                        @if($balanceSheetData['filters']['level_of_detail'] === 'detailed')
                            <td></td>
                        @endif
                        <td class="text-end">
                            <strong>{{ number_format($balanceSheetData['profit_loss'], 2) }}</strong>
                        </td>
                        @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                            @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                                @php
                                    // Calculate comparative P&L - simplified for now
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
                    <tr class="total-row">
                        <td><strong>Total Equity</strong></td>
                        @if($balanceSheetData['filters']['level_of_detail'] === 'detailed')
                            <td></td>
                        @endif
                        <td class="text-end">
                            <strong>{{ number_format($totalEquity, 2) }}</strong>
                        </td>
                        @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                            @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                                @php
                                    $compEquity = collect($comparativeData['equity'] ?? [])->sum(function($item) {
                                        return $item->credit_total - $item->debit_total;
                                    });
                                    
                                    // Simplified comparative P&L calculation
                                    $compPnL = 0; // Default to 0 for comparative P&L
                                    
                                    $compTotalEquity = $compEquity + $compPnL;
                                @endphp
                                <td class="text-end">
                                    <strong>{{ number_format($compTotalEquity, 2) }}</strong>
                                </td>
                            @endforeach
                        @endif
                    </tr>
                </tbody>
            </table>
        @else
            @if(isset($balanceSheetData['profit_loss']))
                <table class="table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            @if($balanceSheetData['filters']['level_of_detail'] === 'detailed')
                                <th>Code</th>
                            @endif
                            <th class="text-end">Current Period</th>
                            @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                                @foreach($balanceSheetData['comparative'] as $columnName => $data)
                                    <th class="text-end">{{ $columnName }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Profit & Loss Section -->
                        <tr class="pnl-row">
                            <td><strong>Profit & Loss</strong></td>
                            @if($balanceSheetData['filters']['level_of_detail'] === 'detailed')
                                <td></td>
                            @endif
                            <td class="text-end">
                                <strong>{{ number_format($balanceSheetData['profit_loss'], 2) }}</strong>
                            </td>
                            @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                                @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                                    @php
                                        // Calculate comparative P&L - simplified for now
                                        $compPnL = 0; // Default to 0 for comparative P&L
                                    @endphp
                                    <td class="text-end">
                                        <strong>{{ number_format($compPnL, 2) }}</strong>
                                    </td>
                                @endforeach
                            @endif
                        </tr>
                        
                        <!-- Total Equity -->
                        <tr class="total-row">
                            <td><strong>Total Equity</strong></td>
                            @if($balanceSheetData['filters']['level_of_detail'] === 'detailed')
                                <td></td>
                            @endif
                            <td class="text-end">
                                <strong>{{ number_format($balanceSheetData['profit_loss'], 2) }}</strong>
                            </td>
                            @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                                @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                                    <td class="text-end">
                                        <strong>{{ number_format(0, 2) }}</strong>
                                    </td>
                                @endforeach
                            @endif
                        </tr>
                    </tbody>
                </table>
            @else
                <p>No equity accounts found.</p>
            @endif
        @endif
    </div>

    <!-- Total Liabilities + Equity Breakdown -->
    <div class="liabilities-equity-breakdown">
        <h3 style="text-align: center; background-color: #17a2b8; color: white; padding: 10px; margin: 20px 0;">TOTAL LIABILITIES + EQUITY BREAKDOWN</h3>
        <table>
            <thead>
                <tr>
                    <th>Component</th>
                    <th class="text-center">Amount</th>
                    @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                        @foreach($balanceSheetData['comparative'] as $columnName => $data)
                            <th class="text-center">{{ $columnName }}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $totalLiabilities = $balanceSheetData['current']['liabilities']->sum(function($item) { return $item->credit_total - $item->debit_total; });
                    $baseEquity = $balanceSheetData['current']['equity']->sum(function($item) { return $item->credit_total - $item->debit_total; });
                    $totalPnL = $balanceSheetData['profit_loss'];
                    $totalEquity = $baseEquity + $totalPnL;
                    $totalLiabilitiesPlusEquity = $totalLiabilities + $totalEquity;
                @endphp
                <tr>
                    <td><strong>Total Liabilities</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalLiabilities, 2) }}</strong></td>
                    @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                        @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                            @php
                                $comparativeLiabilities = collect($comparativeData['liabilities'] ?? [])->sum(function($item) {
                                    return $item->credit_total - $item->debit_total;
                                });
                            @endphp
                            <td class="text-right"><strong>{{ number_format($comparativeLiabilities, 2) }}</strong></td>
                        @endforeach
                    @endif
                </tr>
                <tr>
                    <td><strong>Total Equity (including P&L)</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalEquity, 2) }}</strong></td>
                    @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                        @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                            @php
                                $comparativeEquity = collect($comparativeData['equity'] ?? [])->sum(function($item) {
                                    return $item->credit_total - $item->debit_total;
                                });
                                $comparativePnL = 0; // Simplified for now
                                $comparativeTotalEquity = $comparativeEquity + $comparativePnL;
                            @endphp
                            <td class="text-right"><strong>{{ number_format($comparativeTotalEquity, 2) }}</strong></td>
                        @endforeach
                    @endif
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL LIABILITIES + EQUITY</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalLiabilitiesPlusEquity, 2) }}</strong></td>
                    @if(isset($balanceSheetData['comparative']) && count($balanceSheetData['comparative']) > 0)
                        @foreach($balanceSheetData['comparative'] as $columnName => $comparativeData)
                            @php
                                $comparativeLiabilities = collect($comparativeData['liabilities'] ?? [])->sum(function($item) {
                                    return $item->credit_total - $item->debit_total;
                                });
                                $comparativeEquity = collect($comparativeData['equity'] ?? [])->sum(function($item) {
                                    return $item->credit_total - $item->debit_total;
                                });
                                $comparativeTotal = $comparativeLiabilities + $comparativeEquity;
                            @endphp
                            <td class="text-right"><strong>{{ number_format($comparativeTotal, 2) }}</strong></td>
                        @endforeach
                    @endif
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This is a computer generated document. No signature is required.</p>
        <p>Generated on {{ now()->format('F d, Y \\a\\t g:i A') }}</p>
    </div>
</body>
</html> 