@extends('layouts.main')

@section('title', 'Loan Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN LIST</h6>
        <hr/>

        <!-- Dashboard Stats -->
        <div class="row row-cols-1 row-cols-lg-4">
            <div class="col mb-4">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Loans</p>
                            <h4 class="mb-0">{{ $loans->count() ?? 0 }}</h4>
                        </div>
                        <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money'></i>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loans Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="card-title mb-0">Loans List</h6>
                            <div>
                                <a href="{{ route('loans.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> Create Direct Loan
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap" id="loansTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>Amount</th>
                                        <th>Interest</th>
                                        <th>Total Amount</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Branch</th>
                                        <th>Date Applied</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loans as $index => $loan)
                                    <tr>
                                        <td>{{ $index+1 }}</td>
                                        <td>{{ optional($loan->customer)->name }}</td>
                                        <td>{{ optional($loan->product)->name ?? '-' }}</td>
                                        <td>{{ number_format($loan->amount, 2) }}</td>
                                        <td>{{ number_format($loan->interest_amount, 2) }}</td>
                                        <td>{{ number_format($loan->amount_total, 2) }}</td>
                                        <td>{{ $loan->period }}</td>
                                        <td>{{ $loan->status }}</td>
                                        <td>{{ optional($loan->branch)->name }}</td>
                                        <td>{{ $loan->date_applied }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('loans.show', Hashids::encode($loan->id)) }}" class="btn btn-sm btn-outline-info"><i class="bx bx-show"></i></a>
                                            <a href="{{ route('loans.edit', Hashids::encode($loan->id)) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit"></i></a>
                                            <form action="{{ route('loans.destroy', Hashids::encode($loan->id)) }}" method="POST" class="d-inline-block delete-form" onsubmit="return confirm('Delete this loan?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('#loansTable').DataTable({
            responsive: true,
            order: [[1, 'desc']],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search loans..."
            },
            columnDefs: [
                { targets: -1, responsivePriority: 1, orderable: false, searchable: false },
                { targets: [0,1,2], responsivePriority: 2 }
            ]
        });
    });
</script>
@endpush
