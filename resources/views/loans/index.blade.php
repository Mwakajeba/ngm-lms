@extends('layouts.main')

@section('title', 'Loans')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => '#', 'icon' => 'bx bx-credit-card'],
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">LOAN MANAGEMENT</h6>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#openingBalanceModal">
                    <i class="bx bx-upload me-1"></i> Opening Balance
                </button>
            </div>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- Loan Calculator -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calculator fs-1 text-success"></i>
                                            </div>
                                            <h5 class="card-title">Loan Calculator</h5>
                                            <p class="card-text">Simulate loan scenarios, view schedules and export results.</p>
                                            <a href="{{ route('loan-calculator.index') }}" class="btn btn-success position-relative">
                                                <i class="bx bx-calculator me-1"></i> Open Calculator
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @can('view loans')
                                    <!-- Active Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['active'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-building fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Active Loans</h5>
                                                <p class="card-text">Manage your company loans disbursed to customers.</p>
                                                <a href="{{ route('loans.list') }}" class="btn btn-success position-relative">
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
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['applied'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-plus-circle fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Applied Loans</h5>
                                                <p class="card-text">Manage and initiate loan applications.</p>
                                                <a href="{{ route('loans.by-status', 'applied') }}"
                                                    class="btn btn-success position-relative">
                                                    <i class="bx bx-file-plus me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan

                                @can('view checked loans')
                                    <!-- Checked Applications -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['checked'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-check-circle fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Checked Applications</h5>
                                                <p class="card-text">Manage and check applied loans.</p>
                                                <a href="{{ route('loans.by-status', 'checked') }}"
                                                    class="btn btn-success position-relative">
                                                    <i class="bx bx-check me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view approved loans')
                                    <!-- Approved Applications -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['approved'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-check-circle fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Approved Applications</h5>
                                                <p class="card-text">Manage and verify applied loans.</p>
                                                <a href="{{ route('loans.by-status', 'approved') }}"
                                                    class="btn btn-success position-relative">
                                                    <i class="bx bx-verify me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view authorized loans')
                                    <!-- Authorized Applications -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['authorized'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-badge-check fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Authorized Applications</h5>
                                                <p class="card-text">Manage and approve applied loans.</p>
                                                <a href="{{ route('loans.by-status', 'authorized') }}"
                                                    class="btn btn-success position-relative">
                                                    <i class="bx bx-badge-check me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view defaulted loans')
                                    <!-- Defaulted Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['defaulted'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-error fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Defaulted Loans</h5>
                                                <p class="card-text">Manage all defaulted loans.</p>
                                                <a href="{{ route('loans.by-status', 'defaulted') }}"
                                                    class="btn btn-success position-relative">
                                                    <i class="bx bx-error me-1"></i> View Loans
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view rejected loans')
                                    <!-- Rejected Applications -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['rejected'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-x-circle fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Rejected Applications</h5>
                                                <p class="card-text">Manage all rejected loan applications.</p>
                                                <a href="{{ route('loans.by-status', 'rejected') }}"
                                                    class="btn btn-success position-relative">
                                                    <i class="bx bx-x-circle me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view loans')
                                    <!-- Written Off Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['written_off'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-x-circle fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Written Off Loans</h5>
                                                <p class="card-text">Manage all written off loans.</p>
                                                <a href="{{ route('loans.writtenoff') }}" class="btn btn-success">
                                                    <i class="bx bx-x-circle me-1"></i> View Loans
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view completed loans')
                                    <!-- Completed Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['completed'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-check-circle fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Completed Loans</h5>
                                                <p class="card-text">Manage all completed loans.</p>
                                                <a href="{{ route('loans.by-status', 'completed') }}" class="btn btn-success">
                                                    <i class="bx bx-check-circle me-1"></i> View Loans
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view loans')
                                    <!-- Restructured Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['restructured'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-refresh fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Restructured Loans</h5>
                                                <p class="card-text">Manage all restructured loans.</p>
                                                <a href="{{ route('loans.by-status', 'restructured') }}" class="btn btn-success position-relative">
                                                    <i class="bx bx-refresh me-1"></i> View Loans
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

    <!-- Opening Balance Modal -->
    <div class="modal fade" id="openingBalanceModal" tabindex="-1" aria-labelledby="openingBalanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="openingBalanceModalLabel">Opening Balance - Bulk Loan Creation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="openingBalanceForm" action="{{ route('loans.opening-balance.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <!-- Download Template Button -->
                            <div class="col-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Step 1: Download Template</h6>
                                    <button type="button" id="downloadTemplateBtn" class="btn btn-outline-primary btn-sm">
                                        <i class="bx bx-download me-1"></i> Download Template
                                    </button>
                                </div>
                                <small class="text-muted">Download the CSV template and fill in your loan data</small>
                            </div>

                            <!-- Product Selection -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loan Product <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Product</option>
                                    @foreach($products ?? [] as $product)
                                        <option value="{{ $product->id ?? '' }}" {{ old('product_id') == ($product->id ?? '') ? 'selected' : '' }}>
                                            {{ $product->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Branch Selection -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches ?? [] as $branch)
                                        <option value="{{ $branch->id ?? '' }}" {{ old('branch_id') == ($branch->id ?? '') ? 'selected' : '' }}>
                                            {{ $branch->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Chart Account Selection -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chart Account <span class="text-danger">*</span></label>
                                <select name="chart_account_id"
                                    class="form-select @error('chart_account_id') is-invalid @enderror select2-single" required>
                                    <option value="">Select Chart Account</option>
                                    @foreach($chartAccounts ?? [] as $account)
                                        <option value="{{ $account->id ?? '' }}" {{ old('chart_account_id') == ($account->id ?? '') ? 'selected' : '' }}>
                                            {{ $account->account_name ?? '' }} ({{ $account->account_code ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('chart_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- CSV File Upload -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="csv_file"
                                    class="form-control @error('csv_file') is-invalid @enderror" accept=".csv" required>
                                @error('csv_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Upload the filled CSV template</small>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Instructions:</h6>
                            <ul class="mb-0">
                                <li>Select a loan product first, then download the template</li>
                                <li>Interest cycle will be automatically taken from the selected product</li>
                                <li>Fill in the loan data in the CSV template</li>
                                <li>Ensure customer numbers exist in the system</li>
                                <li>Loans will be created with 'active' status</li>
                                <li>Repayments will be processed automatically if amount_paid > 0</li>
                                <li>Process runs in background - you'll be notified when complete</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-upload me-1"></i> Process Opening Balance
                        </button>
                    </div>
                </form>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const downloadTemplateBtn = document.getElementById('downloadTemplateBtn');
            const productSelect = document.querySelector('select[name="product_id"]');

            downloadTemplateBtn.addEventListener('click', function () {
                const productId = productSelect.value;

                if (!productId) {
                    alert('Please select a loan product first before downloading the template.');
                    productSelect.focus();
                    return;
                }

                // Create download URL with product_id parameter
                const downloadUrl = '{{ route("loans.opening-balance.template") }}?product_id=' + productId;

                // Create a temporary link and trigger download
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = 'opening_balance_template_{{ date("Y-m-d") }}.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
@endpush