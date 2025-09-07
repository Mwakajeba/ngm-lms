@extends('layouts.main')

@section('title', 'Customer Performance Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Customer Reports', 'url' => route('reports.customers'), 'icon' => 'bx bx-group'],
                ['label' => 'Customer Performance Report', 'url' => '#', 'icon' => 'bx bx-trending-up']
            ]" />
            <h6 class="mb-0 text-uppercase">CUSTOMER PERFORMANCE REPORT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Customer Performance Report</h4>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(isset($errors) && $errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    Please fix the following errors:
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Filter Form -->
                            <form method="GET" action="{{ route('reports.customers.performance') }}" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="{{ $startDate }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="{{ $endDate }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="branch_id" class="form-label">Branch</label>
                                        <select class="form-select" id="branch_id" name="branch_id">
                                            <option value="all">All Branches</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="customer_id" class="form-label">Customer</label>
                                        <select class="form-select" id="customer_id" name="customer_id">
                                            <option value="all">All Customers</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} ({{ $customer->customerNo }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="performance_metric" class="form-label">Performance Level</label>
                                        <select class="form-select" id="performance_metric" name="performance_metric">
                                            <option value="all" {{ $performanceMetric == 'all' ? 'selected' : '' }}>All Levels</option>
                                            <option value="excellent" {{ $performanceMetric == 'excellent' ? 'selected' : '' }}>Excellent (90-100)</option>
                                            <option value="good" {{ $performanceMetric == 'good' ? 'selected' : '' }}>Good (70-89)</option>
                                            <option value="average" {{ $performanceMetric == 'average' ? 'selected' : '' }}>Average (50-69)</option>
                                            <option value="poor" {{ $performanceMetric == 'poor' ? 'selected' : '' }}>Poor (0-49)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="risk_level" class="form-label">Risk Level</label>
                                        <select class="form-select" id="risk_level" name="risk_level">
                                            <option value="all" {{ $riskLevel == 'all' ? 'selected' : '' }}>All Risk Levels</option>
                                            <option value="low" {{ $riskLevel == 'low' ? 'selected' : '' }}>Low Risk</option>
                                            <option value="medium" {{ $riskLevel == 'medium' ? 'selected' : '' }}>Medium Risk</option>
                                            <option value="high" {{ $riskLevel == 'high' ? 'selected' : '' }}>High Risk</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bx bx-search me-1"></i> Generate Report
                                        </button>
                                        <a href="{{ route('reports.customers.performance.export-pdf', request()->query()) }}" 
                                           class="btn btn-danger me-2" target="_blank">
                                            <i class="bx bx-file-pdf me-1"></i> Export PDF
                                        </a>
                                        <a href="{{ route('reports.customers.performance.export', request()->query()) }}" 
                                           class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Export Excel
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- Performance Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-md-2">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Total Customers</h6>
                                            <h4 class="text-primary">{{ number_format($performanceData['summary']['total_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">Excellent</h6>
                                            <h4 class="text-success">{{ number_format($performanceData['summary']['excellent_performers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">Good</h6>
                                            <h4 class="text-info">{{ number_format($performanceData['summary']['good_performers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-warning">Average</h6>
                                            <h4 class="text-warning">{{ number_format($performanceData['summary']['average_performers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-danger">Poor</h6>
                                            <h4 class="text-danger">{{ number_format($performanceData['summary']['poor_performers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-secondary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-secondary">Avg Score</h6>
                                            <h4 class="text-secondary">{{ number_format($performanceData['summary']['average_performance_score'], 1) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Risk Level Summary -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">Low Risk</h6>
                                            <h4 class="text-success">{{ number_format($performanceData['summary']['low_risk_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-warning">Medium Risk</h6>
                                            <h4 class="text-warning">{{ number_format($performanceData['summary']['medium_risk_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-danger">High Risk</h6>
                                            <h4 class="text-danger">{{ number_format($performanceData['summary']['high_risk_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Summary -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Total Loan Amount</h6>
                                            <h4 class="text-primary">{{ number_format($performanceData['summary']['total_loan_amount'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">Total Repayments</h6>
                                            <h4 class="text-success">{{ number_format($performanceData['summary']['total_repayments'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">Total Collateral</h6>
                                            <h4 class="text-info">{{ number_format($performanceData['summary']['total_collateral'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Customer No</th>
                                            <th>Customer Name</th>
                                            <th>Branch</th>
                                            <th>Region</th>
                                            <th>Date Registered</th>
                                            <th class="text-center">Total Loans</th>
                                            <th class="text-end">Loan Amount</th>
                                            <th class="text-end">Repayments</th>
                                            <th class="text-end">Collateral</th>
                                            <th class="text-center">Repayment Rate (%)</th>
                                            <th class="text-center">Avg Days Overdue</th>
                                            <th class="text-center">Performance Score</th>
                                            <th>Risk Level</th>
                                            <th class="text-center">Overdue Loans</th>
                                            <th class="text-center">Active Loans</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($performanceData['data'] as $index => $customer)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $customer['customer_no'] }}</td>
                                                <td>{{ $customer['customer_name'] }}</td>
                                                <td>{{ $customer['branch_name'] }}</td>
                                                <td>{{ $customer['region_name'] }}</td>
                                                <td>{{ $customer['date_registered'] ? $customer['date_registered']->format('d/m/Y') : 'N/A' }}</td>
                                                <td class="text-center">{{ $customer['total_loans'] }}</td>
                                                <td class="text-end">{{ number_format($customer['total_loan_amount'], 2) }}</td>
                                                <td class="text-end">{{ number_format($customer['total_repayments'], 2) }}</td>
                                                <td class="text-end">{{ number_format($customer['total_collateral'], 2) }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ 
                                                        $customer['repayment_rate'] >= 80 ? 'success' : 
                                                        ($customer['repayment_rate'] >= 60 ? 'warning' : 'danger') 
                                                    }}">
                                                        {{ number_format($customer['repayment_rate'], 1) }}%
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ 
                                                        $customer['average_days_overdue'] <= 7 ? 'success' : 
                                                        ($customer['average_days_overdue'] <= 30 ? 'warning' : 'danger') 
                                                    }}">
                                                        {{ number_format($customer['average_days_overdue'], 0) }} days
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ 
                                                        $customer['performance_score'] >= 90 ? 'success' : 
                                                        ($customer['performance_score'] >= 70 ? 'info' : 
                                                        ($customer['performance_score'] >= 50 ? 'warning' : 'danger')) 
                                                    }}">
                                                        {{ number_format($customer['performance_score'], 1) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ 
                                                        $customer['risk_level'] === 'low' ? 'success' : 
                                                        ($customer['risk_level'] === 'medium' ? 'warning' : 'danger') 
                                                    }}">
                                                        {{ ucfirst($customer['risk_level']) }} Risk
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $customer['overdue_loans_count'] > 0 ? 'danger' : 'success' }}">
                                                        {{ $customer['overdue_loans_count'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">
                                                        {{ $customer['active_loans_count'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="16" class="text-center text-muted py-4">
                                                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                                                    No customer performance data found for the selected criteria.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
