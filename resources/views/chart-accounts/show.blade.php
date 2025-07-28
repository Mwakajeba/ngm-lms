@extends('layouts.main')

@section('title', 'Chart Account Details')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 text-dark fw-bold">
                                <i class="bx bx-book-open me-2 text-primary"></i>
                                Chart Account Details
                            </h4>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('accounting.accounts.edit', $chartAccount->id) }}" class="btn btn-primary">
                                <i class="bx bx-edit me-1"></i> Edit Account
                            </a>
                            <a href="{{ route('accounting.accounts') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <!-- Left Column - Account Information -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-info-circle me-2"></i>
                                Account Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-hash text-primary fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account ID</small>
                                            <span class="fw-bold text-dark">#{{ $chartAccount->id }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-code text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account Code</small>
                                            <span class="fw-bold text-dark fs-6">{{ $chartAccount->account_code }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-category text-info fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account Class</small>
                                            <span
                                                class="badge bg-primary fs-6">{{ $chartAccount->accountClassGroup->accountClass->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-folder text-warning fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account Group</small>
                                            <span
                                                class="fw-bold text-dark fs-6">{{ $chartAccount->accountClassGroup->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-bookmark text-danger fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account Name</small>
                                            <span class="fw-bold text-dark fs-5">{{ $chartAccount->account_name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Account Flags & Actions -->
                <div class="col-lg-4">
                    <!-- Account Flags Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bx bx-flag me-2 text-muted"></i>
                                Account Flags
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="bx bx-money-withdraw text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block">Cash Flow Impact</small>
                                    @if($chartAccount->has_cash_flow)
                                        <span class="badge bg-success fs-6">Yes</span>
                                        @if($chartAccount->cashFlowCategory)
                                            <br><small class="text-muted">{{ $chartAccount->cashFlowCategory->name }}</small>
                                            @if($chartAccount->cashFlowCategory->description)
                                                <br><small
                                                    class="text-muted fst-italic">{{ $chartAccount->cashFlowCategory->description }}</small>
                                            @endif
                                        @endif
                                    @else
                                        <span class="badge bg-secondary fs-6">No</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="bx bx-pie-chart-alt text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block">Equity Impact</small>
                                    @if($chartAccount->has_equity)
                                        <span class="badge bg-success fs-6">Yes</span>
                                        @if($chartAccount->equityCategory)
                                            <br><small class="text-muted">{{ $chartAccount->equityCategory->name }}</small>
                                            @if($chartAccount->equityCategory->description)
                                                <br><small
                                                    class="text-muted fst-italic">{{ $chartAccount->equityCategory->description }}</small>
                                            @endif
                                        @endif
                                    @else
                                        <span class="badge bg-secondary fs-6">No</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Details Card -->
                    @if($chartAccount->has_cash_flow || $chartAccount->has_equity)
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bx bx-category me-2 text-muted"></i>
                                    Category Details
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                @if($chartAccount->has_cash_flow && $chartAccount->cashFlowCategory)
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bx bx-money-withdraw text-success me-2"></i>
                                            <strong class="text-success">Cash Flow Category</strong>
                                        </div>
                                        <div class="ps-4">
                                            <div class="fw-semibold">{{ $chartAccount->cashFlowCategory->name }}</div>
                                            @if($chartAccount->cashFlowCategory->description)
                                                <small class="text-muted">{{ $chartAccount->cashFlowCategory->description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($chartAccount->has_equity && $chartAccount->equityCategory)
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bx bx-pie-chart-alt text-warning me-2"></i>
                                            <strong class="text-warning">Equity Category</strong>
                                        </div>
                                        <div class="ps-4">
                                            <div class="fw-semibold">{{ $chartAccount->equityCategory->name }}</div>
                                            @if($chartAccount->equityCategory->description)
                                                <small class="text-muted">{{ $chartAccount->equityCategory->description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Quick Actions Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bx bx-cog me-2 text-muted"></i>
                                Quick Actions
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-grid gap-2">
                                <a href="{{ route('accounting.accounts.edit', $chartAccount->id) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit Account
                                </a>
                                <form action="{{ route('accounting.accounts.destroy', $chartAccount->id) }}" method="POST"
                                    class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                        data-name="{{ $chartAccount->account_name }}">
                                        <i class="bx bx-trash me-1"></i> Delete Account
                                    </button>
                                </form>
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
        <p class="mb-0">Copyright © 2021. All right reserved.</p>
    </footer>
@endsection