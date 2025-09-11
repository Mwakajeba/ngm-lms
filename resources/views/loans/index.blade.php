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
                            @can('view loans')
                            <!-- Active Loans -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ $stats['active'] ?? 0 }}</span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-building fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Active Loans</h5>
                                        <p class="card-text">Manage your company loans disbursed to customers.</p>
                                        <a href="{{ route('loans.list') }}" class="btn btn-primary position-relative">
                                            <i class="bx bx-cog me-1"></i> View Loans
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view applied loans')
                            <!-- Applied Loans -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['applied'] ?? 0 }}</span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-plus-circle fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Applied Loans</h5>
                                        <p class="card-text">Manage and initiate loan applications.</p>
                                        <a href="{{ route('loans.by-status', 'applied') }}" class="btn btn-success position-relative">
                                            <i class="bx bx-file-plus me-1"></i> View Applications
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            @can('view checked loans')
                            <!-- Checked Applications -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-teal position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">{{ $stats['checked'] ?? 0 }}</span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-check-circle fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="card-title">Checked Applications</h5>
                                        <p class="card-text">Manage and check applied loans.</p>
                                        <a href="{{ route('loans.by-status', 'checked') }}" class="btn btn-secondary position-relative">
                                            <i class="bx bx-check me-1"></i> View Applications
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view approved loans')
                            <!-- Approved Applications -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-purple position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">{{ $stats['approved'] ?? 0 }}</span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-check-circle fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">Approved Applications</h5>
                                        <p class="card-text">Manage and verify applied loans.</p>
                                        <a href="{{ route('loans.by-status', 'approved') }}" class="btn btn-info position-relative">
                                            <i class="bx bx-verify me-1"></i> View Applications
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view authorized loans')
                            <!-- Authorized Applications -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-orange position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">{{ $stats['authorized'] ?? 0 }}</span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-badge-check fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">Authorized Applications</h5>
                                        <p class="card-text">Manage and approve applied loans.</p>
                                        <a href="{{ route('loans.by-status', 'authorized') }}" class="btn btn-warning position-relative">
                                            <i class="bx bx-badge-check me-1"></i> View Applications
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view defaulted loans')
                            <!-- Defaulted Loans -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $stats['defaulted'] ?? 0 }}</span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-error fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Defaulted Loans</h5>
                                        <p class="card-text">Manage all defaulted loans.</p>
                                        <a href="{{ route('loans.by-status', 'defaulted') }}" class="btn btn-danger position-relative">
                                            <i class="bx bx-error me-1"></i> View Loans
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view rejected loans')
                            <!-- Rejected Applications -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $stats['rejected'] ?? 0 }}</span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-x-circle fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Rejected Applications</h5>
                                        <p class="card-text">Manage all rejected loan applications.</p>
                                        <a href="{{ route('loans.by-status', 'rejected') }}" class="btn btn-danger position-relative">
                                            <i class="bx bx-x-circle me-1"></i> View Applications
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('view writeoff loans')
                            <!-- Written Off Loans -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $stats['written_off'] ?? 0 }}</span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-x-circle fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Written Off Loans</h5>
                                        <p class="card-text">Manage all written off loans.</p>
                                        <a href="{{ route('loans.writtenoff') }}" class="btn btn-danger">
                                            <i class="bx bx-x-circle me-1"></i> View Loans
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
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