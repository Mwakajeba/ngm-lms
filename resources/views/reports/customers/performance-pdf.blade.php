<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Performance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-info {
            font-size: 9px;
            color: #666;
        }
        .summary-section {
            margin-bottom: 15px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin-bottom: 10px;
        }
        .summary-item {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .summary-label {
            font-size: 7px;
            color: #666;
            margin-bottom: 2px;
        }
        .summary-value {
            font-size: 10px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: left;
            font-size: 7px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-success {
            color: #28a745;
        }
        .text-primary {
            color: #007bff;
        }
        .text-warning {
            color: #ffc107;
        }
        .text-info {
            color: #17a2b8;
        }
        .text-secondary {
            color: #6c757d;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .page-break {
            page-break-before: always;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 6px;
            border-radius: 3px;
            margin-bottom: 15px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">CUSTOMER PERFORMANCE REPORT</div>
        <div class="report-info">
            Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} | 
            Generated: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Filter Information -->
    <div class="filter-info">
        <strong>Report Filters:</strong><br>
        Branch: {{ $branchName }} | 
        Customer: {{ $customerName }} | 
        Performance Level: {{ $performanceMetricName }} | 
        Risk Level: {{ $riskLevelName }}
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3>Performance Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Customers</div>
                <div class="summary-value text-primary">{{ number_format($performanceData['summary']['total_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Excellent (90-100)</div>
                <div class="summary-value text-success">{{ number_format($performanceData['summary']['excellent_performers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Good (70-89)</div>
                <div class="summary-value text-info">{{ number_format($performanceData['summary']['good_performers']) }}</div>
            </div>
        </div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Average (50-69)</div>
                <div class="summary-value text-warning">{{ number_format($performanceData['summary']['average_performers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Poor (0-49)</div>
                <div class="summary-value text-danger">{{ number_format($performanceData['summary']['poor_performers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Average Score</div>
                <div class="summary-value text-secondary">{{ number_format($performanceData['summary']['average_performance_score'], 1) }}</div>
            </div>
        </div>
    </div>

    <!-- Risk Level Summary -->
    <div class="summary-section">
        <h3>Risk Level Distribution</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Low Risk</div>
                <div class="summary-value text-success">{{ number_format($performanceData['summary']['low_risk_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Medium Risk</div>
                <div class="summary-value text-warning">{{ number_format($performanceData['summary']['medium_risk_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">High Risk</div>
                <div class="summary-value text-danger">{{ number_format($performanceData['summary']['high_risk_customers']) }}</div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="summary-section">
        <h3>Financial Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Loan Amount</div>
                <div class="summary-value text-primary">{{ number_format($performanceData['summary']['total_loan_amount'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Repayments</div>
                <div class="summary-value text-success">{{ number_format($performanceData['summary']['total_repayments'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Collateral</div>
                <div class="summary-value text-info">{{ number_format($performanceData['summary']['total_collateral'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Performance Details -->
    <div class="performance-section">
        <h3>Customer Performance Details</h3>
        @if($performanceData['data']->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer No</th>
                        <th>Customer Name</th>
                        <th>Branch</th>
                        <th>Region</th>
                        <th>Date Registered</th>
                        <th class="text-center">Total Loans</th>
                        <th class="text-right">Loan Amount</th>
                        <th class="text-right">Repayments</th>
                        <th class="text-right">Collateral</th>
                        <th class="text-center">Repayment Rate (%)</th>
                        <th class="text-center">Avg Days Overdue</th>
                        <th class="text-center">Performance Score</th>
                        <th>Risk Level</th>
                        <th class="text-center">Overdue</th>
                        <th class="text-center">Active</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($performanceData['data'] as $index => $customer)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $customer['customer_no'] }}</td>
                            <td>{{ $customer['customer_name'] }}</td>
                            <td>{{ $customer['branch_name'] }}</td>
                            <td>{{ $customer['region_name'] }}</td>
                            <td>{{ $customer['date_registered'] ? $customer['date_registered']->format('d/m/Y') : 'N/A' }}</td>
                            <td class="text-center">{{ $customer['total_loans'] }}</td>
                            <td class="text-right">{{ number_format($customer['total_loan_amount'], 2) }}</td>
                            <td class="text-right">{{ number_format($customer['total_repayments'], 2) }}</td>
                            <td class="text-right">{{ number_format($customer['total_collateral'], 2) }}</td>
                            <td class="text-center">
                                <span class="{{ 
                                    $customer['repayment_rate'] >= 80 ? 'text-success' : 
                                    ($customer['repayment_rate'] >= 60 ? 'text-warning' : 'text-danger') 
                                }}">
                                    {{ number_format($customer['repayment_rate'], 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ 
                                    $customer['average_days_overdue'] <= 7 ? 'text-success' : 
                                    ($customer['average_days_overdue'] <= 30 ? 'text-warning' : 'text-danger') 
                                }}">
                                    {{ number_format($customer['average_days_overdue'], 0) }}d
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ 
                                    $customer['performance_score'] >= 90 ? 'text-success' : 
                                    ($customer['performance_score'] >= 70 ? 'text-info' : 
                                    ($customer['performance_score'] >= 50 ? 'text-warning' : 'text-danger')) 
                                }}">
                                    {{ number_format($customer['performance_score'], 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ 
                                    $customer['risk_level'] === 'low' ? 'text-success' : 
                                    ($customer['risk_level'] === 'medium' ? 'text-warning' : 'text-danger') 
                                }}">
                                    {{ ucfirst($customer['risk_level']) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ $customer['overdue_loans_count'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $customer['overdue_loans_count'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-info">
                                    {{ $customer['active_loans_count'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px; color: #666;">
                <p>No customer performance data found for the selected criteria.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Branch: {{ $branchName }} | Customer: {{ $customerName }} | Performance: {{ $performanceMetricName }} | Risk: {{ $riskLevelName }}</p>
    </div>
</body>
</html>
