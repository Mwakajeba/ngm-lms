<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income Statement Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
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
        .report-period {
            font-size: 12px;
            margin-bottom: 10px;
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
        .section-header {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }
        .group-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row {
            background-color: #fff3cd;
            font-weight: bold;
        }
        .profit-loss-row {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .account-link {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">INCOME STATEMENT</div>
        <div class="report-period">
            @if($incomeStatementData['start_date'] === $incomeStatementData['end_date'])
                AS AT {{ \Carbon\Carbon::parse($incomeStatementData['end_date'])->format('d-m-Y') }}
            @else
                FROM {{ \Carbon\Carbon::parse($incomeStatementData['start_date'])->format('d-m-Y') }} TO {{ \Carbon\Carbon::parse($incomeStatementData['end_date'])->format('d-m-Y') }}
            @endif
        </div>
        <div class="report-period">Basis: {{ ucfirst($incomeStatementData['reporting_type']) }}</div>
    </div>

    <table>
        <tr class="section-header">
            <td colspan="2">INCOME</td>
        </tr>
        
        @php $sumRevenue = 0; @endphp
        @foreach($incomeStatementData['data']['revenues'] as $groupName => $accounts)
            @php $groupTotal = collect($accounts)->sum('sum'); @endphp
            @if($groupTotal != 0)
                <tr class="group-header">
                    <td colspan="2">{{ $groupName }}</td>
                </tr>
                @foreach($accounts as $chartAccountRevenue)
                    @if($chartAccountRevenue['sum'] != 0)
                        @php $sumRevenue += $chartAccountRevenue['sum']; @endphp
                        <tr>
                            <td>{{ $chartAccountRevenue['account'] }}</td>
                            <td class="text-right">{{ number_format($chartAccountRevenue['sum'], 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            @endif
        @endforeach
        
        <tr class="total-row">
            <td><strong>TOTAL INCOME</strong></td>
            <td class="text-right"><strong>{{ number_format($sumRevenue, 2) }}</strong></td>
        </tr>

        <tr class="section-header">
            <td colspan="2">LESS EXPENSES</td>
        </tr>
        
        @php $sumExpense = 0; @endphp
        @foreach($incomeStatementData['data']['expenses'] as $groupName => $accounts)
            @php $groupTotal = collect($accounts)->sum('sum'); @endphp
            @if($groupTotal != 0)
                <tr class="group-header">
                    <td colspan="2">{{ $groupName }}</td>
                </tr>
                @foreach($accounts as $chartAccountExpenses)
                    @if($chartAccountExpenses['sum'] != 0)
                        @php $sumExpense += $chartAccountExpenses['sum']; @endphp
                        <tr>
                            <td>{{ $chartAccountExpenses['account'] }}</td>
                            <td class="text-right">{{ number_format(abs($chartAccountExpenses['sum']), 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            @endif
        @endforeach
        
        <tr class="total-row">
            <td><strong>TOTAL EXPENSES</strong></td>
            <td class="text-right"><strong>{{ number_format(abs($sumExpense), 2) }}</strong></td>
        </tr>
        
        <tr class="profit-loss-row">
            <td><strong>PROFIT / LOSS</strong></td>
            <td class="text-right"><strong>{{ number_format($sumRevenue - abs($sumExpense), 2) }}</strong></td>
        </tr>
    </table>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <p><strong>Report Generated:</strong> {{ now()->format('d-m-Y H:i:s') }}</p>
        <p><strong>Generated By:</strong> {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>
</html> 