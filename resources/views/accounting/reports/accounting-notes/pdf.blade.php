<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accounting Notes Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-date {
            font-size: 11px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .section-header {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }
        .subsection-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .indent {
            padding-left: 20px;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">ACCOUNTING NOTES</div>
        <div class="report-date">AS AT {{ \Carbon\Carbon::parse($accountingNotesData['as_of_date'])->format('d-m-Y') }}</div>
    </div>

    <table>
        <!-- 1. Significant Accounting Policies -->
        <tr class="section-header">
            <td colspan="2">1. SIGNIFICANT ACCOUNTING POLICIES</td>
        </tr>
        
        @foreach($accountingNotesData['accounting_policies'] as $policy => $details)
            <tr class="subsection-header">
                <td colspan="2">{{ $policy }}</td>
            </tr>
            <tr>
                <td colspan="2">{{ $details['description'] }}</td>
            </tr>
            @foreach($details['details'] as $detail)
                <tr>
                    <td width="5%"></td>
                    <td class="indent">• {{ $detail }}</td>
                </tr>
            @endforeach
            <tr><td colspan="2" style="border: none; height: 10px;"></td></tr>
        @endforeach

        <!-- 2. Significant Transactions -->
        <tr class="section-header">
            <td colspan="2">2. SIGNIFICANT TRANSACTIONS</td>
        </tr>
        
        @if(count($accountingNotesData['significant_transactions']) > 0)
            <tr class="subsection-header">
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
        <tr class="section-header">
            <td colspan="2">3. CONTINGENT LIABILITIES</td>
        </tr>
        
        @foreach($accountingNotesData['contingent_liabilities'] as $liability)
            <tr class="subsection-header">
                <td colspan="2">{{ $liability['description'] }}</td>
            </tr>
            <tr>
                <td colspan="2">{{ $liability['notes'] }}</td>
            </tr>
            <tr><td colspan="2" style="border: none; height: 10px;"></td></tr>
        @endforeach

        <!-- 4. Related Party Transactions -->
        <tr class="section-header">
            <td colspan="2">4. RELATED PARTY TRANSACTIONS</td>
        </tr>
        
        @foreach($accountingNotesData['related_party_transactions'] as $transaction)
            <tr class="subsection-header">
                <td colspan="2">{{ $transaction['party_name'] }} - {{ $transaction['transaction_type'] }}</td>
            </tr>
            <tr>
                <td colspan="2">{{ $transaction['notes'] }}</td>
            </tr>
            <tr><td colspan="2" style="border: none; height: 10px;"></td></tr>
        @endforeach

        <!-- 5. Post-Balance Sheet Events -->
        <tr class="section-header">
            <td colspan="2">5. POST-BALANCE SHEET EVENTS</td>
        </tr>
        
        @foreach($accountingNotesData['post_balance_sheet_events'] as $event)
            <tr class="subsection-header">
                <td colspan="2">{{ $event['event_description'] }}</td>
            </tr>
            <tr>
                <td colspan="2">{{ $event['notes'] }}</td>
            </tr>
            <tr><td colspan="2" style="border: none; height: 10px;"></td></tr>
        @endforeach
    </table>

    <div style="margin-top: 30px; font-size: 9px; color: #666;">
        <p><strong>Report Generated:</strong> {{ now()->format('d-m-Y H:i:s') }}</p>
        <p><strong>Generated By:</strong> {{ auth()->user()->name ?? 'System' }}</p>
        <p><strong>Basis of Preparation:</strong> {{ ucfirst($accountingNotesData['reporting_type']) }}</p>
    </div>
</body>
</html> 