@extends('layouts.main')

@section('title', 'Loan Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-wallet'],
            ['label' => 'Loan Details', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />

        <h6 class="mb-3 text-uppercase">Loan Details - {{ $loan->customer->name }}</h6>

        <!-- Nav tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" data-bs-toggle="tab" href="#loan_detail" role="tab">Loan Detail</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#guarantors" role="tab">Guarantors</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#collaterals" role="tab">Collaterals</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#documents" role="tab">Documents</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#repayments" role="tab">Repayments</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#schedule" role="tab">Schedule</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane fade show active" id="loan_detail">
                <div class="tab-pane fade show active" id="loan_detail" role="tabpanel">

                    <div class="text-muted">
                        <div class="table-responsive">
                            <table class="table table-border mb-0">
                                <tbody>
                                    <!-- Left Column -->
                                    <tr>
                                        <th scope="row">Customer Name</th>
                                        <td>{{ $loan->customer->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Disbursed On</th>
                                        <td>{{ \Carbon\Carbon::parse($loan->disbursed_on)->format('M d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Product</th>
                                        <td>{{ $loan->product->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">First Repayment</th>
                                        <td>{{ \Carbon\Carbon::parse($loan->first_repayment_date)->format('M d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Amount</th>
                                        <td>{{ number_format($loan->amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Last Repayment</th>
                                        <td>{{ \Carbon\Carbon::parse($loan->last_repayment_date)->format('M d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Interest Method</th>
                                        <td>{{ $loan->product->interest_method }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Bank Account</th>
                                        <td>{{ $loan->bankAccount->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Interest Amount</th>
                                        <td>{{ number_format($loan->interest_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Group</th>
                                        <td>{{ $loan->group->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Total Repayable</th>
                                        <td>{{ number_format($loan->amount_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Branch</th>
                                        <td>{{ $loan->branch->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Period</th>
                                        <td>{{ $loan->period }} months</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Applied On</th>
                                        <td>{{ \Carbon\Carbon::parse($loan->date_applied)->format('M d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Sector</th>
                                        <td>{{ $loan->sector }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Status</th>
                                        <td>{{ ucfirst($loan->status) }}</td>
                                    </tr>
                                </tbody>
                            </table>

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

@push('scripts')
<script>
    $(document).ready(function() {
        $('#loansTableDetail').DataTable({
            responsive: true,
            order: [
                [1, 'asc']
            ],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search loans..."
            },
            columnDefs: [{
                    targets: -1,
                    responsivePriority: 1,
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [0, 1, 2],
                    responsivePriority: 2
                }
            ]
        });
    });
</script>
@endpush