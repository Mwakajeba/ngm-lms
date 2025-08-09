@extends('layouts.main')

@section('title', 'Loans Reports')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Loans Reports', 'url' => '#', 'icon' => 'bx bx-credit-card']
            ]" />
        <h6 class="mb-0 text-uppercase">LOANS REPORTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Loans Reports</h4>
                        <p class="text-muted">Loans reports functionality will be implemented here.</p>

                        <div class="row">
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-credit-card fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Loan Portfolio Report</h5>
                                        <p class="card-text">Comprehensive overview of all active loans and their status.</p>
                                        <button class="btn btn-primary" disabled>
                                            <i class="bx bx-file me-1"></i> Coming Soon
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-trending-up fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Loan Performance Report</h5>
                                        <p class="card-text">Analyze loan performance metrics and repayment trends.</p>
                                        <button class="btn btn-success" disabled>
                                            <i class="bx bx-file me-1"></i> Coming Soon
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-error-circle fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">Delinquency Report</h5>
                                        <p class="card-text">Track overdue loans and payment delinquencies.</p>
                                        <button class="btn btn-warning" disabled>
                                            <i class="bx bx-file me-1"></i> Coming Soon
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-error-circle fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">Loan Disbursement Report</h5>
                                        <p class="card-text">Access a detailed summary of all loans that have been successfully disbursed to clients.</p>
                                        <a href="{{ route('accounting.loans.reports.disbursed') }}" class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection