@extends('layouts.main')

@section('title', 'Customer Management')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Customers', 'url' => '#', 'icon' => 'bx bx-group']
        ]" />
            <h6 class="mb-0 text-uppercase">CUSTOMER LIST</h6>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Customers</p>
                                <h4 class="mb-0">{{ $customers->count() ?? 0 }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-group'></i>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Additional cards if needed (e.g. by region, gender) --}}
            </div>

            <!-- Customers Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="card-title mb-0">Customers List</h6>
                                <div>
                                    <a href="{{ route('customers.bulk-upload') }}" class="btn btn-success me-2">
                                        <i class="bx bx-upload"></i> Bulk Upload
                                    </a>
                                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                        <i class="bx bx-plus"></i> Add Customer
                                    </a>
                                </div>
                            </div>

                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap" id="customersTable">
                                <thead>
                                    <tr>
                                        <th>Customer No</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Region</th>
                                        <th>District</th>
                                        <th>Branch</th>
                                        <th>Company</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($customers as $customer)
                                    <tr>
                                        <td>{{ $customer->customerNo }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->phone1 }}</td>
                                        <td>{{ $customer->region->name ?? '' }}</td>
                                        <td>{{ $customer->district->name ?? '' }}</td>
                                        <td>{{ optional($customer->branch)->name }}</td>
                                        <td>{{ optional($customer->company)->name }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('customers.show', Hashids::encode($customer->id)) }}" class="btn btn-sm btn-outline-info"><i class="bx bx-show"></i></a>
                                            <a href="{{ route('customers.edit',  Hashids::encode($customer->id)) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit"></i></a>
                                            <form action="{{ route('customers.destroy',  Hashids::encode($customer->id)) }}" method="POST" class="d-inline-block delete-form" onsubmit="return confirm('Delete this customer?');">
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
        $(document).ready(function () {
            $('#customersTable').DataTable({
                responsive: true,
                order: [[1, 'asc']],
                pageLength: 10,
                language: {
                    search: "",
                    searchPlaceholder: "Search customers..."
                },
                columnDefs: [
                    { targets: -1, responsivePriority: 1, orderable: false, searchable: false },
                    { targets: [0, 1, 2], responsivePriority: 2 }
                ]
            });
        });
    </script>
@endpush
