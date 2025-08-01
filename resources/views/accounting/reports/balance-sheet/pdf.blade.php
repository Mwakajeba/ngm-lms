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
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
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
        .report-subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        .filters {
            margin-bottom: 20px;
            font-size: 10px;
        }
        .filters table {
            width: 100%;
            border-collapse: collapse;
        }
        .filters td {
            padding: 2px 10px;
            border: none;
        }
        .filters .label {
            font-weight: bold;
            width: 120px;
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
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        .section-header {
            background-color: #333;
            color: white;
            font-weight: bold;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .balance-check {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">BALANCE SHEET</div>
        <div class="report-subtitle">As of {{ \Carbon\Carbon::parse($balanceSheetData['as_of_date'])->format('F d, Y') }}</div>
    </div>

    <div class="filters">
        <table>
            <tr>
                <td class="label">Reporting Type:</td>
                <td>{{ ucfirst($balanceSheetData['reporting_type']) }} Basis</td>
                <td class="label">Level of Detail:</td>
                <td>{{ ucfirst($balanceSheetData['level_of_detail']) }}</td>
            </tr>
            <tr>
                <td class="label">Comparative Years:</td>
                <td>{{ $balanceSheetData['comparative_years'] }} Year(s)</td>
                <td class="label">Generated:</td>
                <td>{{ now()->format('F d, Y \a\t g:i A') }}</td>
            </tr>
        </table>
    </div>

    <!-- Assets Section -->
    <div class="section-header">ASSETS</div>
    <table>
        <thead>
            <tr>
                <th>Account/Group</th>
                <th class="text-center">Current Period</th>
                @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
                    <th class="text-center">{{ $i }} Year{{ $i > 1 ? 's' : '' }} Ago</th>
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
                        @if($balanceSheetData['level_of_detail'] === 'detailed')
                            <strong>{{ $asset->account_name }}</strong><br>
                            <small>{{ $asset->account_code }}</small>
                        @else
                            <strong>{{ $asset->group_name }}</strong>
                        @endif
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($currentAmount, 2) }}</strong>
                    </td>
                    @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
                        @php
                            $comparativeAsset = $balanceSheetData['comparative'][$i]->first(function($item) use ($asset) {
                                return $balanceSheetData['level_of_detail'] === 'detailed' 
                                    ? $item->account_id == $asset->account_id
                                    : $item->group_id == $asset->group_id;
                            });
                            $comparativeAmount = $comparativeAsset ? ($comparativeAsset->debit_total - $comparativeAsset->credit_total) : 0;
                        @endphp
                        <td class="text-right">
                            {{ number_format($comparativeAmount, 2) }}
                        </td>
                    @endfor
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL ASSETS</strong></td>
                <td class="text-right">
                    <strong>{{ number_format($totalAssets, 2) }}</strong>
                </td>
                @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
                    <td class="text-right">
                        <strong>{{ number_format($balanceSheetData['comparative'][$i]->filter(function($item) {
                            return in_array(strtolower($item->class_name), ['assets', 'asset']);
                        })->sum(function($item) {
                            return $item->debit_total - $item->credit_total;
                        }), 2) }}</strong>
                    </td>
                @endfor
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
                @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
                    <th class="text-center">{{ $i }} Year{{ $i > 1 ? 's' : '' }} Ago</th>
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
                        @if($balanceSheetData['level_of_detail'] === 'detailed')
                            <strong>{{ $liability->account_name }}</strong><br>
                            <small>{{ $liability->account_code }}</small>
                        @else
                            <strong>{{ $liability->group_name }}</strong>
                        @endif
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($currentAmount, 2) }}</strong>
                    </td>
                    @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
                        @php
                            $comparativeLiability = $balanceSheetData['comparative'][$i]->first(function($item) use ($liability) {
                                return $balanceSheetData['level_of_detail'] === 'detailed' 
                                    ? $item->account_id == $liability->account_id
                                    : $item->group_id == $liability->group_id;
                            });
                            $comparativeAmount = $comparativeLiability ? ($comparativeLiability->credit_total - $comparativeLiability->debit_total) : 0;
                        @endphp
                        <td class="text-right">
                            {{ number_format($comparativeAmount, 2) }}
                        </td>
                    @endfor
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL LIABILITIES</strong></td>
                <td class="text-right">
                    <strong>{{ number_format($totalLiabilities, 2) }}</strong>
                </td>
                @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
                    <td class="text-right">
                        <strong>{{ number_format($balanceSheetData['comparative'][$i]->filter(function($item) {
                            return in_array(strtolower($item->class_name), ['liabilities', 'liability']);
                        })->sum(function($item) {
                            return $item->credit_total - $item->debit_total;
                        }), 2) }}</strong>
                    </td>
                @endfor
            </tr>
        </tbody>
    </table>

    <!-- Equity Section -->
    <div class="section">
        <h3>EQUITY</h3>
        @if($balanceSheetData['current']['equity']->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Account</th>
                        @if($balanceSheetData['level_of_detail'] === 'detailed')
                            <th>Code</th>
                        @endif
                        <th class="text-end">Current Period</th>
                        @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
                            <th class="text-end">{{ $i }} Year{{ $i > 1 ? 's' : '' }} Ago</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($balanceSheetData['current']['equity'] as $item)
                        @php
                            $currentBalance = $item->credit_total - $item->debit_total;
                        @endphp
                        <tr>
                            <td>{{ $balanceSheetData['level_of_detail'] === 'detailed' ? $item->account_name : $item->group_name }}</td>
                            @if($balanceSheetData['level_of_detail'] === 'detailed')
                                <td>{{ $item->account_code }}</td>
                            @endif
                            <td class="text-end">{{ number_format($currentBalance, 2) }}</td>
                            @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
                                @php
                                    $compData = collect($balanceSheetData['comparative'][$i] ?? [])->first(function($comp) use ($item) {
                                        return $balanceSheetData['level_of_detail'] === 'detailed' 
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
                    <tr class="pnl-row">
                        <td><strong>Profit & Loss</strong></td>
                        @if($balanceSheetData['level_of_detail'] === 'detailed')
                            <td></td>
                        @endif
                        <td class="text-end">
                            <strong>{{ number_format($balanceSheetData['profit_loss'], 2) }}</strong>
                        </td>
                        @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
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
                    <tr class="total-row">
                        <td><strong>Total Equity</strong></td>
                        @if($balanceSheetData['level_of_detail'] === 'detailed')
                            <td></td>
                        @endif
                        <td class="text-end">
                            <strong>{{ number_format($totalEquity, 2) }}</strong>
                        </td>
                        @for($i = 1; $i <= $balanceSheetData['comparative_years']; $i++)
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
        @else
            <p>No equity accounts found.</p>
        @endif
    </div>

    <!-- Balance Check -->
    <div class="balance-check">
        <strong>Balance Check:</strong><br>
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
        Assets ({{ number_format($totalAssets, 2) }}) = 
        Liabilities ({{ number_format($totalLiabilities, 2) }}) + 
        Equity ({{ number_format($totalEquity, 2) }})
        <br><small>Where Equity includes P&L ({{ number_format($totalPnL, 2) }})</small>
        = {{ number_format($rightSide, 2) }}
        @if($totalAssets == $rightSide)
            <br><strong>✅ Balance sheet is balanced</strong>
        @else
            <br><strong>⚠️ Balance sheet is not balanced</strong>
        @endif
    </div>

    <div class="footer">
        <p>This is a computer generated document. No signature is required.</p>
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
    </div>
</body>
</html> 