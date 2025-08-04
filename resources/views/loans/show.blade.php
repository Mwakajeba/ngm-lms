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

        <h5 class="mb-4 text-uppercase">Loan Details - {{ $loan->customer->name }}</h5>

        <ul class="nav nav-tabs nav-tabs-style-2 mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" data-bs-toggle="tab" href="#loan_detail" role="tab">Details</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#schedule" role="tab">Schedule</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#guarantors" role="tab">Guarantors</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#documents" role="tab">Documents</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#collateral" role="tab">Collaterals</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#repayment" role="tab">Repayments</a>
            </li>
        </ul>

        <div class="tab-content py-3">
            <div class="tab-pane fade show active" id="loan_detail" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-striped align-middle">
                                <tbody>
                                    @foreach([
                                    ['label' => 'Customer Name', 'value' => $loan->customer->name],
                                    ['label' => 'Product', 'value' => $loan->product->name],
                                    ['label' => 'Amount', 'value' => number_format($loan->amount, 2)],
                                    ['label' => 'Interest Amount', 'value' => number_format($loan->interest_amount, 2)],
                                    ['label' => 'Total Repayable', 'value' => number_format($loan->amount_total, 2)],
                                    ['label' => 'Period', 'value' => $loan->period . ' months'],
                                    ['label' => 'Interest Method', 'value' => $loan->product->interest_method],
                                    ['label' => 'Status', 'value' => ucfirst($loan->status)],
                                    ['label' => 'Disbursed On', 'value' => \Carbon\Carbon::parse($loan->disbursed_on)->format('M d, Y')],
                                    ['label' => 'First Repayment', 'value' => \Carbon\Carbon::parse($loan->first_repayment_date)->format('M d, Y')],
                                    ['label' => 'Last Repayment', 'value' => \Carbon\Carbon::parse($loan->last_repayment_date)->format('M d, Y')],
                                    ['label' => 'Applied On', 'value' => \Carbon\Carbon::parse($loan->date_applied)->format('M d, Y')],
                                    ['label' => 'Branch', 'value' => $loan->branch->name ?? 'N/A'],
                                    ['label' => 'Group', 'value' => $loan->group->name ?? 'N/A'],
                                    ['label' => 'Bank Account', 'value' => $loan->bankAccount->name ?? 'N/A'],
                                    ['label' => 'Sector', 'value' => $loan->sector],
                                    ] as $item)
                                    <tr>
                                        <th scope="row" class="col-sm-4 text-muted">{{ $item['label'] }}</th>
                                        <td class="fw-bold text-dark">{{ $item['value'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="schedule" role="tabpanel">
                @if($loan->schedule->count())
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 text-dark">Loan Schedule</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr class="table-light">
                                        <th scope="col" class="text-secondary ps-4">Due Date</th>
                                        <th scope="col" class="text-secondary">Principal</th>
                                        <th scope="col" class="text-secondary">Interest</th>
                                        <th scope="col" class="text-secondary text-end pe-4">Total Installment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->schedule as $item)
                                    <tr>
                                        <td class="ps-4">{{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}</td>
                                        <td>{{ number_format($item->principal, 2) }}</td>
                                        <td>{{ number_format($item->interest, 2) }}</td>
                                        <td class="text-end pe-4">{{ number_format($item->principal + $item->interest, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center p-5 border rounded bg-light">
                    <h4 class="text-muted">No repayment schedule available.</h4>
                    <p class="text-secondary">A schedule will be generated once the loan is approved.</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="guarantors" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Guarantors</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addGuarantorModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill me-2" viewBox="0 0 16 16">
                            <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                            <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z" />
                        </svg>
                        Add Guarantor
                    </button>
                </div>

                @if($loan->guarantors && $loan->guarantors->count())
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-secondary ps-4">#</th>
                                        <th scope="col" class="text-secondary">Name</th>
                                        <th scope="col" class="text-secondary">Phone</th>
                                        <th scope="col" class="text-secondary text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->guarantors as $index => $guarantor)
                                    <tr>
                                        <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                        <td>{{ $guarantor->name }}</td>
                                        <td>{{ $guarantor->phone }}</td>
                                        <td class="text-end pe-4">
                                            <form action="{{ route('loans.removeGuarantor', [$loan->id, $guarantor->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this guarantor?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center p-5 border rounded bg-light">
                    <h4 class="text-muted">No guarantors assigned to this loan.</h4>
                    <p class="text-secondary">Click the button above to add a guarantor.</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="documents" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Documents</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload me-2" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V9.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z" />
                        </svg>
                        Upload Document
                    </button>
                </div>

                @if($loan->loanFiles->count())
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-secondary ps-4">#</th>
                                        <th scope="col" class="text-secondary">Document Name</th>
                                        <th scope="col" class="text-secondary text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loan->loanFiles as $index => $doc)
                                    <tr>
                                        <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                        <td>{{ $doc->fileType->name }}</td>
                                        <td class="text-end pe-4">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary me-2">
                                                View
                                            </a>
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" download class="btn btn-sm btn-primary">
                                                Download
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center p-5 border rounded bg-light">
                    <h4 class="text-muted">No documents uploaded yet.</h4>
                    <p class="text-secondary">Click the button above to add the first document.</p>
                </div>
                @endif
            </div>

            {{-- You will need to add the other tabs (Collaterals, Repayments, etc.) here in the same consistent style --}}
        </div>

        <div class="modal fade" id="addGuarantorModal" tabindex="-1" aria-labelledby="addGuarantorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('loans.addGuarantor', $loan->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Guarantor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                            <div class="mb-3">
                                <label for="guarantor_id" class="form-label">Select Guarantor</label>
                                <select class="form-select" name="guarantor_id" id="guarantor_id" required>
                                    <option value="">-- Choose Guarantor --</option>
                                    @foreach($guarantorCustomers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone1 }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="relation" class="form-label">Relation to Borrower</label>
                                <input type="text" class="form-control" name="relation" id="relation" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Add Guarantor</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('loan-documents.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
                    @csrf
                    <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadDocumentLabel">Upload Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="docName" class="form-label">Document Name</label>
                            <input type="text" class="form-control" name="name" id="docName" required>
                        </div>
                        <div class="mb-3">
                            <label for="docFile" class="form-label">Choose File</label>
                            <input type="file" class="form-control" name="file" id="docFile" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>
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