@extends('layouts.main')

@section('title', 'Loans')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => '#', 'icon' => 'bx bx-credit-card'],
        ]" />
            <h6 class="mb-0 text-uppercase">LOAN MANAGEMENT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- Active Loans -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-building fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Active Loans</h5>
                                            <p class="card-text">Manage your company loans disbursed to customers.</p>
                                            <a href="{{ route('loans.list') }}" class="btn btn-primary">
                                                <i class="bx bx-cog me-1"></i> View Loans
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Loan Applications -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-plus-circle fs-1 text-success"></i>
                                            </div>
                                            <h5 class="card-title">Loan Applications</h5>
                                            <p class="card-text">Manage and initiate loan applications.</p>
                                            <a href="{{ route('loans.application.index') }}" class="btn btn-success">
                                                <i class="bx bx-file-plus me-1"></i> View Applications
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Checked Applications -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-teal">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-check-circle fs-1 text-secondary"></i>
                                            </div>
                                            <h5 class="card-title">Checked Applications</h5>
                                            <p class="card-text">Manage and check applied loans.</p>
                                            <a href="{{ route('loans.application.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-check me-1"></i> View Applications
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Verified Applications -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-purple">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-check-circle fs-1 text-info"></i>
                                            </div>
                                            <h5 class="card-title">Approved Applications</h5>
                                            <p class="card-text">Manage and verify applied loans.</p>
                                            <a href="{{ route('loans.application.index') }}" class="btn btn-info">
                                                <i class="bx bx-verify me-1"></i> View Applications
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approved Applications -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-orange">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-badge-check fs-1 text-warning"></i>
                                            </div>
                                            <h5 class="card-title">Authorized Applications</h5>
                                            <p class="card-text">Manage and approve applied loans.</p>
                                            <a href="{{ route('loans.application.index') }}" class="btn btn-warning">
                                                <i class="bx bx-badge-check me-1"></i> View Applications
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Defaulted Loans -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-error fs-1 text-danger"></i>
                                            </div>
                                            <h5 class="card-title">Defaulted Loans</h5>
                                            <p class="card-text">Manage all defaulted loans.</p>
                                            <a href="{{ route('loans.list') }}" class="btn btn-danger">
                                                <i class="bx bx-error me-1"></i> View Loans
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
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
    </footer>

@endsection

@push('styles')
    <style>
        .border-purple {
            border-color: #6f42c1 !important;
        }

        .text-purple {
            color: #6f42c1 !important;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }

        .btn-purple:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
            color: white;
        }

        .border-orange {
            border-color: #fd7e14 !important;
        }

        .text-orange {
            color: #fd7e14 !important;
        }

        .btn-orange {
            background-color: #fd7e14;
            border-color: #fd7e14;
            color: white;
        }

        .btn-orange:hover {
            background-color: #e8690b;
            border-color: #e8690b;
            color: white;
        }

        .border-teal {
            border-color: #20c997 !important;
        }

        .text-teal {
            color: #20c997 !important;
        }

        .btn-teal {
            background-color: #20c997;
            border-color: #20c997;
            color: white;
        }

        .btn-teal:hover {
            background-color: #1ba37e;
            border-color: #1ba37e;
            color: white;
        }

        .border-danger {
            border-color: #dc3545 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #bb2d3b;
            color: white;
        }
    </style>
@endpush