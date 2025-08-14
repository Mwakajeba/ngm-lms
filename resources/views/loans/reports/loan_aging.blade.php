@extends('layouts.main')

@section('title', 'Loan Aging Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
            ['label' => 'Loan Aging Report', 'url' => '#', 'icon' => 'bx bx-timer']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN AGING REPORT</h6>
        <hr />

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-timer me-2"></i>Loan Aging Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.loans.reports.loan_aging') }}">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="as_of_date" class="form-label">As of Date</label>
                            <input type="date" class="form-control" id="as_of_date" name="as_of_date" value="{{ request('as_of_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($agingData))
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Aging Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                             <tr>
                                <th>Customer</th>
                                <th>Customer No</th>
                                <th>Phone</th>
                                <th>Loan No</th>
                                <th>Amount</th>
                                <th>Outstanding Balance</th>
                                <th>Disbursed Date</th>
                                <th>Expiry</th>
                                <th>Branch</th>
                                <th>Current</th>
                                <th>1-30 Days</th>
                                <th>31-60 Days</th>
                                <th>61-90 Days</th>
                                <th>91+ Days</th>
                                <th>Total Overdue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agingData as $row)
                                <tr>
                                    <td>{{ $row['customer'] }}</td>
                                    <td>{{ $row['customer_no'] }}</td>
                                    <td>{{ $row['phone'] }}</td>
                                    <td>{{ $row['loan_no'] }}</td>
                                    <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['outstanding_balance'], 2) }}</td>
                                    <td>{{ $row['disbursed_no'] }}</td>
                                    <td>{{ $row['expiry'] }}</td>
                                    <td>{{ $row['branch'] }}</td>
                                    <td class="text-end">{{ number_format($row['current'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['bucket_1_30'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['bucket_31_60'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['bucket_61_90'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['bucket_91_plus'], 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($row['total_overdue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No aging data found for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
