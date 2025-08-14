@php
use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', $pageTitle ?? 'Loan Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => $pageTitle ?? 'Loan List', 'url' => '#', 'icon' => 'bx bx-list']
        ]" />
        <h6 class="mb-0 text-uppercase">{{ $pageTitle ?? 'LOAN LIST' }}</h6>
        <hr />

        <!-- Dashboard Stats -->
        <div class="row row-cols-1 row-cols-lg-4">
            <div class="col mb-4">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">{{ $pageTitle ?? 'Total Loans' }}</p>
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
                        @can('create loan')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="card-title mb-0">{{ $pageTitle ?? 'Loans List' }}</h6>
                            <div>
                                @if(isset($status) && $status === 'applied')
                                <a href="{{ route('loans.application.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> Create Loan Application
                                </a>
                                @else
                                <a href="{{ route('loans.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> Create Direct Loan
                                </a>
                                @endif
                            </div>
                        </div>
                        @endcan

                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap table-striped" id="loansTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>Amount</th>
                                        <th>Interest Rate</th>
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
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ optional($loan->customer)->name }}</td>
                                        <td>{{ optional($loan->product)->name ?? '-' }}</td>
                                        <td>{{ number_format($loan->amount, 2) }}</td>
                                        <td>{{ $loan->interest }}%</td>
                                        <td>{{ number_format($loan->amount_total, 2) }}</td>
                                        <td>{{ $loan->period }}</td>
                                        <td>
                                            @switch($loan->status)
                                            @case('applied')
                                            <span class="badge bg-warning">Applied</span>
                                            @break
                                            @case('checked')
                                            <span class="badge bg-info">Checked</span>
                                            @break
                                            @case('approved')
                                            <span class="badge bg-primary">Approved</span>
                                            @break
                                            @case('authorized')
                                            <span class="badge bg-success">Authorized</span>
                                            @break
                                            @case('active')
                                            <span class="badge bg-success">Active</span>
                                            @break
                                            @case('defaulted')
                                            <span class="badge bg-danger">Defaulted</span>
                                            @break
                                            @case('rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @break
                                            @default
                                            <span class="badge bg-secondary">{{ ucfirst($loan->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ optional($loan->branch)->name }}</td>
                                        <td>{{ $loan->date_applied }}</td>
                                        <td class="text-center">

                                            @can('view loan details')
                                            <a href="{{ route('loans.show', Hashids::encode($loan->id)) }}"
                                                class="btn btn-sm btn-outline-info">
                                                View
                                            </a>
                                            @endcan
                                            @can('edit loan')

                                            <a href="{{ route('loans.edit', Hashids::encode($loan->id)) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>

                                            @endcan

                                            @can('delete loan')

                                            <form action="{{ route('loans.destroy', Hashids::encode($loan->id)) }}"
                                                method="POST" class="d-inline-block delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteLoan({{ $loan->id }})">
                                                    <i class="bx bx-trash me-1"></i>Delete
                                                </button>
                                            </form>

                                            @endcan

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
            order: [
                [1, 'desc']
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

    function deleteLoan(loanId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/loans/${loanId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush