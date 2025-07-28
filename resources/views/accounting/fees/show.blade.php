@extends('layouts.main')

@section('title', 'Fee Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">FEE DETAILS</h6>
                    <p class="text-muted mb-0">View fee information</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('accounting.fees.edit', $fee) }}" class="btn btn-warning">
                            Edit Fee
                        </a>
                        <a href="{{ route('accounting.fees.index') }}" class="btn btn-secondary">
                            Back to Fees
                        </a>
                    </div>
                </div>
            </div>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">{{ $fee->name }}</h4>
                                <div>
                                    {!! $fee->status_badge !!}
                                    {!! $fee->fee_type_badge !!}
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="btn-group" role="group">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">
                                                Change Status
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#"
                                                        onclick="changeStatus('active')">Activate</a></li>
                                                <li><a class="dropdown-item" href="#"
                                                        onclick="changeStatus('inactive')">Deactivate</a></li>
                                            </ul>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger delete-fee-btn"
                                            data-fee-id="{{ $fee->id }}" data-fee-name="{{ $fee->name }}">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">Basic Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold" width="40%">Fee Name:</td>
                                            <td>{{ $fee->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Chart Account:</td>
                                            <td>{{ $fee->chartAccount->name ?? 'N/A' }}
                                                ({{ $fee->chartAccount->account_code ?? 'N/A' }})</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Fee Type:</td>
                                            <td>{!! $fee->fee_type_badge !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Amount:</td>
                                            <td class="fw-bold text-primary">{{ $fee->formatted_amount }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>{!! $fee->status_badge !!}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">Organization Details</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold" width="40%">Company:</td>
                                            <td>{{ $fee->company->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Branch:</td>
                                            <td>{{ $fee->branch->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Created By:</td>
                                            <td>{{ $fee->createdBy->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Created Date:</td>
                                            <td>{{ $fee->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Last Updated:</td>
                                            <td>{{ $fee->updated_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($fee->description)
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h5 class="text-primary mb-3">Description</h5>
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                {{ $fee->description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for status change -->
    <form id="statusForm" method="POST" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" id="statusInput" name="status">
    </form>

@endsection

@push('scripts')
    <script>
        function changeStatus(status) {
            const statusLabels = {
                'active': 'Active',
                'inactive': 'Inactive'
            };

            Swal.fire({
                title: 'Change Status',
                text: `Are you sure you want to change the status to ${statusLabels[status]}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('statusInput').value = status;
                    document.getElementById('statusForm').action = '{{ route("accounting.fees.changeStatus", $fee) }}';
                    document.getElementById('statusForm').submit();
                }
            });
        }

        // Delete fee with SweetAlert confirmation
        $('.delete-fee-btn').on('click', function () {
            const feeId = $(this).data('fee-id');
            const feeName = $(this).data('fee-name');

            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete fee "${feeName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': `/accounting/fees/${feeId}`
                    });
                    form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                    form.append($('<input>', { 'type': 'hidden', 'name': '_method', 'value': 'DELETE' }));
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    </script>
@endpush