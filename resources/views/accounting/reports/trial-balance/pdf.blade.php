<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
        }
        .filters {
            margin-bottom: 20px;
            font-size: 10px;
        }
        .filters table {
            width: 100%;
        }
        .filters td {
            padding: 2px 10px;
        }
        .label {
            font-weight: bold;
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
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .section-header {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding: 5px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .balance-check {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .positive {
            color: #28a745;
        }
        .negative {
            color: #dc3545;
        }
        .account-code {
            font-family: monospace;
        }
        .balance {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="panel-body" style="background: #fff">
        <div id="printingArea">
            <table id="myTable">
                <tr>
                    <td colspan="{{ $trialBalanceData['layout'] === 'multiple' ? 9 : ($trialBalanceData['layout'] === 'double' ? 4 : 3) }}" 
                        style="text-align: center; font-weight:bold">
                        {{ $company->name ?? 'SmartFinance' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="{{ $trialBalanceData['layout'] === 'multiple' ? 9 : ($trialBalanceData['layout'] === 'double' ? 4 : 3) }}" 
                        style="text-align: center; font-weight:bold">
                        TRIAL BALANCE REPORT
                    </td>
                </tr>
                <tr>
                    <td colspan="{{ $trialBalanceData['layout'] === 'multiple' ? 9 : ($trialBalanceData['layout'] === 'double' ? 4 : 3) }}" 
                        style="text-align: center; font-weight:bold">
                        @if($trialBalanceData['start_date'] === $trialBalanceData['end_date'])
                            AS AT {{ \Carbon\Carbon::parse($trialBalanceData['end_date'])->format('d-m-Y') }}
                        @else
                            FROM {{ \Carbon\Carbon::parse($trialBalanceData['start_date'])->format('d-m-Y') }} TO {{ \Carbon\Carbon::parse($trialBalanceData['end_date'])->format('d-m-Y') }}
                        @endif
                    </td>
                </tr>

                @if($trialBalanceData['layout'] === 'double')
                    <tr style="font-weight:bold">
                        <th>ACCOUNT NAME</th>
                        <th>ACCOUNT CODE</th>
                        <th>DEBIT</th>
                        <th>CREDIT</th>
                    </tr>
                    @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp
                    @foreach($trialBalanceData['data'] as $item)
                        @if($item->debit_total > 0 || $item->credit_total > 0)
                            <tr>
                                <td>{{ $item->account_name }}</td>
                                <td class="account-code">{{ $item->account_code }}</td>
                                <td class="balance">
                                    @if($item->debit_total > 0)
                                        {{ number_format($item->debit_total, 2) }}
                                        @php $totalDebit += $item->debit_total; @endphp
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="balance">
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
                    <tr style="font-weight: bold">
                        <td colspan="2" style="text-align: right;">TOTAL</td>
                        <td class="balance">{{ number_format($totalDebit, 2) }}</td>
                        <td class="balance">{{ number_format($totalCredit, 2) }}</td>
                    </tr>
                    <tr style="font-weight: bold">
                        <td colspan="2" style="text-align: right;">Net Balance (Debit - Credit)</td>
                        <td colspan="2" class="balance"
                            style="color: {{ ($totalDebit - $totalCredit) == 0 ? 'green' : 'red' }};">
                            {{ number_format($totalDebit - $totalCredit, 2) }}
                        </td>
                    </tr>
                @elseif($trialBalanceData['layout'] === 'single')
                    <tr style="font-weight:bold">
                        <th>ACCOUNT NAME</th>
                        <th>ACCOUNT CODE</th>
                        <th style="text-align: right">BALANCE</th>
                    </tr>
                    @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp
                    @foreach($trialBalanceData['data'] as $item)
                        @if($item->balance != 0)
                            <tr>
                                <td>{{ $item->account_name }}</td>
                                <td class="account-code">{{ $item->account_code }}</td>
                                <td class="balance">
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
                    <tr style="font-weight: bold">
                        <td colspan="2" style="text-align: right;">Net Balance (Debit - Credit)</td>
                        <td class="balance"
                            style="color: {{ ($totalDebit - $totalCredit) == 0 ? 'green' : 'red' }};">
                            {{ number_format($totalDebit - $totalCredit, 2) }}
                        </td>
                    </tr>
                @else
                    <tr style="font-weight:bold">
                        <th rowspan="2">ACCOUNT NAME</th>
                        <th rowspan="2">ACCOUNT CODE</th>
                        <th colspan="2">OPENING BALANCES</th>
                        <th colspan="2">CURRENT YEAR CHANGES</th>
                        <th colspan="2">CLOSING BALANCES</th>
                        <th rowspan="2">DIFFERENCE</th>
                    </tr>
                    <tr style="font-weight:bold">
                        <th>DR</th>
                        <th>CR</th>
                        <th>DR</th>
                        <th>CR</th>
                        <th>DR</th>
                        <th>CR</th>
                    </tr>
                    
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
                                <td class="account-code">{{ $item->account_code }}</td>
                                
                                <td class="balance">{{ $openingDiff > 0 ? number_format($openingDiff, 2) : '-' }}</td>
                                <td class="balance">{{ $openingDiff < 0 ? number_format(abs($openingDiff), 2) : '-' }}</td>
                                
                                <td class="balance">{{ $changeDiff > 0 ? number_format($changeDiff, 2) : '-' }}</td>
                                <td class="balance">{{ $changeDiff < 0 ? number_format(abs($changeDiff), 2) : '-' }}</td>
                                
                                <td class="balance">{{ $closingDiff > 0 ? number_format($closingDiff, 2) : '-' }}</td>
                                <td class="balance">{{ $closingDiff < 0 ? number_format(abs($closingDiff), 2) : '-' }}</td>
                                
                                <td class="balance">
                                    {{ $difference > 0 ? number_format($difference, 2) : ($difference < 0 ? number_format(abs($difference), 2) : '-') }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    
                    <tr style="font-weight: bold">
                        <td colspan="2" style="text-align: right;">TOTAL</td>
                        <td class="balance">{{ number_format($totalOpeningDr, 2) }}</td>
                        <td class="balance">{{ number_format($totalOpeningCr, 2) }}</td>
                        <td class="balance">{{ number_format($totalChangeDr, 2) }}</td>
                        <td class="balance">{{ number_format($totalChangeCr, 2) }}</td>
                        <td class="balance">{{ number_format($totalClosingDr, 2) }}</td>
                        <td class="balance">{{ number_format($totalClosingCr, 2) }}</td>
                        <td class="balance">
                            {{ $totalDiff > 0 ? number_format($totalDiff, 2) : ($totalDiff < 0 ? number_format(abs($totalDiff), 2) : '-') }}
                        </td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer generated document. No signature is required.</p>
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
    </div>
</body>
</html> 