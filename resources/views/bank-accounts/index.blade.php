@extends('layouts.main')

@section('title', 'Bank Accounts')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 text-dark fw-bold">
                                <i class="bx bx-bank me-2 text-primary"></i>
                                Bank Accounts
                            </h4>
                        </div>
                        <div>
                            <a href="{{ route('accounting.bank-accounts.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus"></i> Add New Bank Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="bankAccountsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Bank Name</th>
                                            <th>Account Number</th>
                                            <th>Chart Account</th>
                                            <th>Account Class</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bankAccounts as $bankAccount)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">#{{ $bankAccount->id }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-light rounded-circle p-2 me-3">
                                                            <i class="bx bx-bank text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold text-dark">{{ $bankAccount->name }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ $bankAccount->account_number }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-info">{{ $bankAccount->chartAccount->account_name ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-secondary">{{ $bankAccount->chartAccount->accountClassGroup->accountClass->name ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <small
                                                        class="text-muted">{{ $bankAccount->created_at->format('M d, Y') }}</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('accounting.bank-accounts.show', $bankAccount->id) }}"
                                                            class="btn btn-sm btn-outline-info" title="View Details">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="{{ route('accounting.bank-accounts.edit', $bankAccount->id) }}"
                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <form
                                                            action="{{ route('accounting.bank-accounts.destroy', $bankAccount->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                title="Delete" data-name="{{ $bankAccount->name }}">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-bank fs-1 mb-3"></i>
                                                        <p class="mb-0">No bank accounts found</p>
                                                        <small>Create your first bank account to get started</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#bankAccountsTable').DataTable({
                responsive: true,
                order: [[0, 'desc']]
            });

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