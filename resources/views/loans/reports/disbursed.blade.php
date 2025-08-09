@extends('layouts.main')

@section('title', 'Loans')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => '#', 'icon' => 'bx bx-credit-card'],
            ['label' => 'Loan Disbursement Report', 'url' => '#', 'icon' => 'bx bx-dollar-circle']
        ]" />

        <!-- Report Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0"><i class="bx bx-dollar-circle me-2"></i>Loan Disbursement Report</h5>
                            <small class="text-muted">Generate and export detailed loan disbursement records.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <form id="loanDisbursementForm" method="GET" action="{{ route('accounting.loans.reports.disbursed') }}">
                                <div class="relative inline-block text-left">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search me-1"></i> Apply Filters
                                    </button>
                                </div>
                            </form>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                        <i class="bx bx-file-pdf me-2"></i> Export PDF
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">
                                        <i class="bx bx-file me-2"></i> Export Excel
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section (inside card-body) -->
                        <form id="loanDisbursementForm" method="GET" action="{{ route('accounting.loans.reports.disbursed') }}">
                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}">
                                </div>
                                <!-- End Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}">
                                </div>
                                <!-- Branch -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Company -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="company_id" class="form-label">Company</label>
                                    <select class="form-select" id="company_id" name="company_id">
                                        <option value="">All Companies</option>
                                        @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bx bx-search me-1"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($disbursements))
        <!-- Report Summary Cards -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Report Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="border-l-4 border-blue-500 rounded-lg p-4 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-500">Total Amount Disbursed</p>
                                    <h3 class="text-2xl font-bold mt-1 text-blue-600">
                                        {{ number_format($summary['total_disbursed'] ?? 0, 2) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="border-l-4 border-emerald-500 rounded-lg p-4 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-500">Number of Loans</p>
                                    <h3 class="text-2xl font-bold mt-1 text-emerald-600">
                                        {{ number_format($summary['loan_count'] ?? 0) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="border-l-4 border-purple-500 rounded-lg p-4 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-500">Average Disbursed Amount</p>
                                    <h3 class="text-2xl font-bold mt-1 text-purple-600">
                                        {{ number_format($summary['average_disbursed'] ?? 0, 2) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Disbursement Details</h6>
                    </div>
                    <div class="card-body">
                        @if(isset($disbursements) && count($disbursements) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Disbursement Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Loan ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Customer Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Loan Product
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Disbursed Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($disbursements as $disbursement)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($disbursement->disbursement_date)->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                            {{ $disbursement->loan_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $disbursement->customer->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $disbursement->product->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                            {{ number_format($disbursement->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $disbursement->branch->name }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                            <i class="bx bx-info-circle text-4xl"></i>
                                            <p class="mt-2">No loan disbursement data found for the selected criteria.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bx bx-info-circle fs-1 text-muted"></i>
                            <p class="mt-2 text-muted">No loan disbursement data found for the selected criteria.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function exportReport(type) {
        const form = document.getElementById('loanDisbursementForm');
        const formData = new FormData(form);
        formData.append('export_type', type);

        const url = '{{ route("accounting.loans.reports.loan-export") }}?' + new URLSearchParams(Object.fromEntries(formData));

        Swal.fire({
            title: 'Generating Report...',
            html: 'Please wait while we prepare your ' + type.toUpperCase() + ' report.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        window.location.href = url;
    }
</script>
@endsection
