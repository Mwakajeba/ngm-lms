<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Loan Disbursement Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            /* Font size ndogo zaidi kwa A3 */
            margin: 0;
            padding: 0;
        }

        /*--- Header/Kichwa ---*/
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header p {
            margin: 0;
            padding: 0;
            color: #666;
            font-size: 10px;
        }

        /*--- Jedwali ---*/
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            word-wrap: break-word;
            /* Hakikisha maandishi hayavuki nje ya cell */
            font-size: 9px;
        }

        th {
            background-color: #f2f2f2;
            color: #555;
            font-weight: bold;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /*--- Alignment ---*/
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /*--- Footer ---*/
        .footer {
            width: 100%;
            margin-top: 20px;
            text-align: right;
        }

        .footer p {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin: 0;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Loan Disbursement Report</h1>
        <p><strong>FROM:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
        <p><strong>BRANCH:</strong> {{ $branch->name ?? 'ALL' }}</p>
        <p><strong>REPORT DATE:</strong> {{ \Carbon\Carbon::now()->format('M d, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th scope="col" style="width: 8%">A/C NO.</th>
                <th scope="col" style="width: 8%">Disbursement Date</th>
                <th scope="col" style="width: 5%">Period</th>
                <th scope="col" style="width: 10%">Customer Name</th>
                <th scope="col" style="width: 8%">Customer No</th>
                <th scope="col" style="width: 8%">Loan Product</th>
                <th scope="col" style="width: 8%">Loan No</th>
                <th scope="col" style="width: 8%">Disbursed Amount</th>
                <th scope="col" style="width: 8%">Interest Amount</th>
                <th scope="col" style="width: 8%">Amount to Pay</th>
                <th scope="col" style="width: 5%">Interest Rate</th>
                <th scope="col" style="width: 8%">End Date</th>
                <th scope="col" style="width: 8%">Loan Officer</th>
            </tr>
        </thead>
        <tbody>
            @php
            $totalDisbursed = 0;
            $totalInterest = 0;
            $totalToPay = 0;
            @endphp
            @foreach($disbursements as $disbursement)

                <td>{{ \Carbon\Carbon::parse($disbursement->disbursed_on)->format('M d, Y') }}</td>
                <td>{{ $disbursement->period }}</td>
                <td>{{ $disbursement->customer->name ?? 'N/A' }}</td>
                <td>{{ $disbursement->customer->customerNo ?? 'N/A' }}</td>
                <td>{{ $disbursement->product->name ?? 'N/A' }}</td>
                <td>{{ $disbursement->loanNo ?? 'N/A'}}</td>
                <td class="text-right">{{ number_format($disbursement->amount, 2) }}</td>
                <td class="text-right">{{ number_format($disbursement->interest_amount, 2) }}</td>
                <td class="text-right">{{ number_format($disbursement->amount_total, 2) }}</td>
                <td class="text-right">{{ number_format($disbursement->interest, 2) }} %</td>
                <td>{{ \Carbon\Carbon::parse($disbursement->last_repayment_date)->format('M d, Y') }}</td>
                <td>{{ $disbursement->loanOfficer->name ?? 'N/A' }}</td>
            </tr>
            @php
            $totalDisbursed += $disbursement->amount;
            $totalInterest += $disbursement->interest_amount;
            $totalToPay += $disbursement->amount_total;
            @endphp
            @endforeach
            <tr>
                <td colspan="6" class="text-right"><strong>TOTALS</strong></td>
                <td class="text-right"><strong>{{ number_format($totalDisbursed, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalInterest, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalToPay, 2) }}</strong></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

</body>

</html>