<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Voucher #{{ $receiptVoucher->reference }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            color: #333;
            font-size: 11px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-left {
            display: flex;
            align-items: center;
        }
        .logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin-right: 12px;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 2px;
        }
        .document-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .header-right {
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .voucher-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .info-section {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 4px;
        }
        .info-row {
            margin-bottom: 6px;
        }
        .info-value {
            color: #333;
            font-size: 10px;
            line-height: 1.3;
        }
        .amount-section {
            text-align: right;
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .amount-label {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .amount-value {
            font-size: 24px;
            font-weight: bold;
        }
        .line-items {
            margin-bottom: 20px;
        }
        .line-items h3 {
            color: #28a745;
            font-size: 14px;
            margin-bottom: 10px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #28a745;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e9ecef;
            font-size: 10px;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #28a745;
        }
        .notes-section {
            margin-bottom: 20px;
        }
        .notes-section h3 {
            color: #28a745;
            font-size: 14px;
            margin-bottom: 10px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 5px;
        }
        .notes-content {
            background-color: #f8f9fa;
            padding: 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        .footer {
            margin-top: 30px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .signature-box {
            text-align: center;
            flex: 1;
            margin: 0 10px;
        }
        .signature-line {
            width: 100%;
            height: 1px;
            background-color: #333;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if($receiptVoucher->user->company->logo)
                <img src="{{ asset('storage/' . $receiptVoucher->user->company->logo) }}" alt="Company Logo" class="logo">
            @endif
            <div class="company-info">
                <div class="company-name">{{ $receiptVoucher->user->company->name ?? 'SmartFinance' }}</div>
                <div class="document-title">RECEIPT VOUCHER</div>
            </div>
        </div>
        <div class="header-right">
            <div>Generated on {{ date('F d, Y \a\t g:i A') }}</div>
            <div>Voucher #{{ $receiptVoucher->reference }}</div>
        </div>
    </div>

    <div class="voucher-info">
        <div class="info-section">
            <div class="section-title">Voucher Details</div>
            <div class="info-row">
                <div class="info-label">Reference</div>
                <div class="info-value">{{ $receiptVoucher->reference }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Date</div>
                <div class="info-value">{{ $receiptVoucher->formatted_date }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Bank Account</div>
                <div class="info-value">
                    {{ $receiptVoucher->bankAccount->name ?? 'N/A' }}<br>
                    <small>{{ $receiptVoucher->bankAccount->account_number ?? 'N/A' }}</small>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="section-title">Payee Information</div>
            <div class="info-row">
                <div class="info-label">Payee Type</div>
                <div class="info-value">
                    <span style="color: #28a745; font-weight: bold;">
                        {{ ucfirst($receiptVoucher->payee_type ?? 'N/A') }}
                    </span>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Payee</div>
                <div class="info-value">
                    @if($receiptVoucher->payee_type === 'customer' && $receiptVoucher->customer)
                        {{ $receiptVoucher->customer->name ?? 'N/A' }}<br>
                        <small>{{ $receiptVoucher->customer->customerNo ?? 'N/A' }}</small>
                    @elseif($receiptVoucher->payee_type === 'other')
                        {{ $receiptVoucher->payee_name ?? 'N/A' }}
                    @else
                        <span style="color: #999;">No payee information</span>
                    @endif
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Branch</div>
                <div class="info-value">{{ $receiptVoucher->branch->name ?? 'N/A' }}</div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="section-title">Notes</div>
            
            <div class="info-row">
                <div class="info-label">Description</div>
                <div class="info-value">
                    {{ $receiptVoucher->description ?: 'No description provided' }}
                </div>
            </div>
        </div>
    </div>

    <div class="amount-section">
        <div class="amount-label">Total Amount Received</div>
        <div class="amount-value">TZS {{ number_format($receiptVoucher->amount, 2) }}</div>
    </div>

    <div class="line-items">
        <h3>Receipt Details</h3>
        <table>
            <thead>
                <tr>
                    <th width="8%">#</th>
                    <th width="50%">Account</th>
                    <th width="25%">Description</th>
                    <th width="17%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receiptVoucher->receiptItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->chartAccount->account_name ?? 'N/A' }}</strong><br>
                            <small>{{ $item->chartAccount->account_code ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $item->description ?: 'No description' }}</td>
                        <td style="text-align: right;">TZS {{ number_format($item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">
                            No line items found
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                    <td style="text-align: right;"><strong>TZS {{ number_format($receiptVoucher->amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="margin-top: 5px; font-size: 12px;">Received From</div>
                <div style="margin-top: 8px; font-size: 10px; color: #666;">
                    @if($receiptVoucher->payee_type === 'customer' && $receiptVoucher->customer)
                        {{ $receiptVoucher->customer->name }}
                    @elseif($receiptVoucher->payee_type === 'other')
                        {{ $receiptVoucher->payee_name }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
            <p>This is a computer generated document. No signature is required.</p>
            <p>Receipt Voucher #{{ $receiptVoucher->reference }} | Generated on {{ date('F d, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html> 