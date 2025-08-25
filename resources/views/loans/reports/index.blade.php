@extends('layouts.main')

@section('title', 'Loans Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h1 class="page-title">Loans Reports</h1>
                <p class="text-muted">Loans reports functionality will be implemented here.</p>
            </div>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="row">
        <!-- Loan Portfolio Report -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="icon-box mb-3">
                        <i class="fas fa-credit-card fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Loan Portfolio Report</h5>
                    <p class="card-text text-muted">Comprehensive overview of all active loans and their status.</p>
                    <a href="{{ route('accounting.loans.reports.portfolio') }}" class="btn btn-primary">
                        <i class="fas fa-file-alt me-1"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Loan Performance Report -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="icon-box mb-3">
                        <i class="fas fa-chart-line fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Loan Performance Report</h5>
                    <p class="card-text text-muted">Analyze loan performance metrics and repayment trends.</p>
                    <a href="{{ route('accounting.loans.reports.performance') }}" class="btn btn-success">
                        <i class="fas fa-chart-bar me-1"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Delinquency Report -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="icon-box mb-3">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title">Delinquency Report</h5>
                    <p class="card-text text-muted">Track overdue loans and payment delinquencies.</p>
                    <a href="{{ route('accounting.loans.reports.delinquency') }}" class="btn btn-warning">
                        <i class="fas fa-exclamation-circle me-1"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Loan Arrears Report -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="icon-box mb-3">
                        <i class="fas fa-clock fa-3x text-danger"></i>
                    </div>
                    <h5 class="card-title">Loan Arrears Report</h5>
                    <p class="card-text text-muted">Track loan arrears and overdue amounts.</p>
                    <a href="{{ route('accounting.loans.reports.arrears') }}" class="btn btn-danger">
                        <i class="fas fa-file-alt me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Expected vs Collected Report -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="icon-box mb-3">
                        <i class="fas fa-balance-scale fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">Expected vs Collected</h5>
                    <p class="card-text text-muted">Compare expected amounts from schedule against actual collections.</p>
                    <a href="{{ route('accounting.loans.reports.expected_vs_collected') }}" class="btn btn-info">
                        <i class="fas fa-file-alt me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Portfolio at Risk (PAR) Report -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="icon-box mb-3">
                        <i class="fas fa-shield-alt fa-3x text-dark"></i>
                    </div>
                    <h5 class="card-title">Portfolio at Risk (PAR)</h5>
                    <p class="card-text text-muted">Analyze portfolio risk based on loan arrears.</p>
                    <a href="{{ route('accounting.loans.reports.portfolio_at_risk') }}" class="btn btn-dark">
                        <i class="fas fa-file-alt me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Internal Portfolio Analysis -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="icon-box mb-3">
                        <i class="fas fa-analytics fa-3x text-secondary"></i>
                    </div>
                    <h5 class="card-title">Internal Portfolio Analysis</h5>
                    <p class="card-text text-muted">Conservative portfolio analysis for internal risk management.</p>
                    <a href="{{ route('accounting.loans.reports.internal_portfolio_analysis') }}" class="btn btn-secondary">
                        <i class="fas fa-file-alt me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-box {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    transition: transform 0.2s;
    border: 1px solid #e3e6f0;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.btn {
    padding: 8px 20px;
    border-radius: 5px;
}
</style>
@endsection
