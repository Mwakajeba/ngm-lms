@extends('layouts.main')

@section('title', 'Cash Collaterals')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Deposit Accounts', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" />
        <h6 class="mb-0 text-uppercase">DEPOSIT ACCOUNTS</h6>
        <hr />

        <!-- Stats Card -->
        <div class="row row-cols-1 row-cols-lg-4 mb-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Collaterals</p>
                            <h4 class="mb-0">{{ $cashCollaterals->count() }}</h4>
                        </div>
                        <div class="ms-3">
                            <div class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-wallet font-size-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Deposits Accounts List</h4>
                    @can('create cash collateral')
                    <a href="{{ route('cash_collaterals.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Add Account
                    </a>
                    @endcan
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" id="collateralTable">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cashCollaterals as $collateral)
                            <tr>
                                <td>{{ $collateral->customer->name ?? 'N/A' }}</td>
                                <td>{{ $collateral->type->name ?? 'N/A' }}</td>
                                <td>{{ number_format($collateral->amount, 2) }}</td>
                                <td>{{ $collateral->created_at->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">

                                        @can('deposit cash collateral')

                                        <a href="{{ route('cash_collaterals.deposit',Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-primary">
                                            Deposit
                                        </a>

                                        @endcan

                                        @can('withdraw cash collateral')

                                        <a href="{{ route('cash_collaterals.withdraw', Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-success">
                                            Withdraw
                                        </a>
                                        @endcan
                                        @can('view cash collateral details')
                                        <a href="{{ route('cash_collaterals.show', Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-outline-info">View</a>
                                        @endcan

                                        @can('edit cash collateral')
                                        <a href="{{ route('cash_collaterals.edit', Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                        @endcan

                                        @can('delete cash collateral')
                                        <form action="{{ route('cash_collaterals.destroy', Hashids::encode($collateral->id)) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-name="{{ $collateral->id }}">Delete</button>
                                        </form>
                                        @endcan
                                    </div>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#collateralTable').DataTable({
            responsive: true,
            order: [
                [0, 'asc']
            ],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search collaterals..."
            },
            columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false,
                    responsivePriority: 1
                },
                {
                    targets: [0, 1],
                    responsivePriority: 2
                }
            ]
        });
    });
</script>
@endpush