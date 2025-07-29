@extends('layouts.main')

@section('title', 'Bank Accounts')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="row row-cols-1 row-cols-lg-3">
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="mb-0">Total</p>
                                        <h4 class="font-weight-bold">{{ $bankAccounts->total() }}</h4>
                                    </div>
                                    <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-dollar'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="mb-0">Total Balance</p>
                                        <h4 class="font-weight-bold">{{ number_format($bankAccounts->sum('balance'), 0, ',', '.') }}</h4>
                                    </div>
                                    <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-wallet'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end row-->

                <h6 class="mb-0 text-uppercase">BANK ACCOUNTS</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Bank Accounts</h5>
                            <a href="{{ route('accounting.bank-accounts.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus"></i> Add New Bank Account
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table id="bankAccountsTable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Bank Name</th>
                                        <th>Account Number</th>
                                        <th>Chart Account</th>
                                        <th>Account Class</th>
                                        <th>Account Group</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bankAccounts as $index => $bankAccount)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $bankAccount->name }}</td>
                                            <td>{{ $bankAccount->account_number }}</td>
                                            <td>{{ $bankAccount->chartAccount->account_name ?? 'N/A' }}</td>
                                            <td>{{ $bankAccount->chartAccount->accountClassGroup->accountClass->name ?? 'N/A' }}
                                            </td>
                                            <td>{{ $bankAccount->chartAccount->accountClassGroup->name ?? 'N/A' }}</td>
                                            <td>{{ $bankAccount->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('accounting.bank-accounts.show', $bankAccount->id) }}"
                                                    class="btn btn-sm btn-info">View</a>
                                                <a href="{{ route('accounting.bank-accounts.edit', $bankAccount->id) }}"
                                                    class="btn btn-sm btn-primary">Edit</a>

                                                <form action="{{ route('accounting.bank-accounts.destroy', $bankAccount->id) }}"
                                                    method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        data-name="{{ $bankAccount->name }}">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $bankAccounts->links() }}
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

@push('scripts')
    <script>
        $(document).ready(function () {
            // Check if DataTable is already initialized
            if (!$.fn.DataTable.isDataTable('#bankAccountsTable')) {
                $('#bankAccountsTable').DataTable({
                    responsive: true,
                    order: [[0, 'asc']]
                });
            }

            // Delete confirmation
            $('.delete-form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const name = form.find('button[type="submit"]').data('name');

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form[0].submit();
                    }
                });
            });
        });
    </script>
@endpush