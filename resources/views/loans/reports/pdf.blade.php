<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Loan Disbursement Report</title>
        <style>
            body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; }
            h1 { text-align: center; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; color: #555; }
            .text-right { text-align: right; }
        </style>
    </head>
    <body>

        <h1>Loan Disbursement Report</h1>
        <p>Report Date: {{ \Carbon\Carbon::now()->format('M d, Y') }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>

        <table>
            <thead>
                <tr>
                    <th>Disbursement Date</th>
                    <th>Period</th>
                    <th>Customer Name</th>
                    <th>Application Date</th>
                    <th>Loan Product</th>
                    <th class="text-right">Disbursed Amount</th>
                    <th>Amount To Pay</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                @foreach($disbursements as $disbursement)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($disbursement->disbursed_on)->format('M d, Y') }}</td>
                    <td>{{ $disbursement->period }} Months</td>
                    <td>{{ $disbursement->customer->name ?? 'N/A' }}</td>
                    <td>{{ $disbursement->date_applied }}</td>
                    <td>{{ $disbursement->product->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($disbursement->amount, 2) }}</td>
                    <td>{{ number_format($disbursement->amount_total, 2) }}</td>
                    <td>{{ $disbursement->branch->name ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </body>
    </html>
    