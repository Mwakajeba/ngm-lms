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

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold text-dark mb-0">Loan Details for {{ $loan->customer->name }}</h4>
           
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1 text-uppercase fw-bold text-secondary">Loan Status</h5>
                    <span class="badge bg-primary fs-6">{{ ucfirst($loan->status) }}</span>
                </div>
                <div class="text-end">
                    <p class="mb-1 fw-bold text-dark">{{ $loan->repayment_progress }}% Complete</p>
                    <div class="progress" style="width: 250px; height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $loan->repayment_progress }}%;" aria-valuenow="{{ $loan->repayment_progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-style-2 mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active d-flex align-items-center" data-bs-toggle="tab" href="#loan_detail" role="tab">
                    <i class="bx bx-info-circle me-2 font-18"></i>Details
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#schedule" role="tab">
                    <i class="bx bx-calendar me-2 font-18"></i>Schedule
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#guarantors" role="tab">
                    <i class="bx bx-group me-2 font-18"></i>Guarantors
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#documents" role="tab">
                    <i class="bx bx-file me-2 font-18"></i>Documents
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#repayments" role="tab">
                    <i class="bx bx-credit-card me-2 font-18"></i>Repayments
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#collaterals" role="tab">
                    <i class="bx bx-shield me-2 font-18"></i>Collaterals
                </a>
            </li>
        </ul>

        <div class="tab-content py-3">
            <div class="tab-pane fade show active" id="loan_detail" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 text-dark fw-bold">Loan Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach([
                            ['label' => 'Customer Name', 'value' => $loan->customer->name, 'icon' => 'bx bx-user'],
                            ['label' => 'Product', 'value' => $loan->product->name, 'icon' => 'bx bx-package'],
                            ['label' => 'Amount', 'value' => number_format($loan->amount, 2), 'icon' => 'bx bx-money'],
                            ['label' => 'Interest Amount', 'value' => number_format($loan->interest_amount, 2), 'icon' => 'bx bx-trending-up'],
                            ['label' => 'Total Repayable', 'value' => number_format($loan->amount_total, 2), 'icon' => 'bx bx-calculator'],
                            ['label' => 'Period', 'value' => $loan->period . ' months', 'icon' => 'bx bx-time'],
                            ['label' => 'Interest Method', 'value' => $loan->product->interest_method, 'icon' => 'bx bx-bar-chart-alt-2'],
                            ['label' => 'Interest Rate', 'value' => $loan->interest ?? 'N/A', 'icon' => 'bx bx-bar-chart-alt-2'],
                            ['label' => 'Disbursed On', 'value' => \Carbon\Carbon::parse($loan->disbursed_on)->format('M d, Y'), 'icon' => 'bx bx-calendar-check'],
                            ['label' => 'First Repayment', 'value' => \Carbon\Carbon::parse($loan->first_repayment_date)->format('M d, Y'), 'icon' => 'bx bx-calendar-event'],
                            ['label' => 'Last Repayment', 'value' => \Carbon\Carbon::parse($loan->last_repayment_date)->format('M d, Y'), 'icon' => 'bx bx-calendar-minus'],
                            ['label' => 'Applied On', 'value' => \Carbon\Carbon::parse($loan->date_applied)->format('M d, Y'), 'icon' => 'bx bx-calendar-plus'],
                            ['label' => 'Branch', 'value' => $loan->branch->name ?? 'N/A', 'icon' => 'bx bx-building'],
                            ['label' => 'Group', 'value' => $loan->group->name ?? 'N/A', 'icon' => 'bx bx-group'],
                            ['label' => 'Bank Account', 'value' => $loan->bankAccount->name ?? 'N/A', 'icon' => 'bx bx-bank'],
                            ['label' => 'Sector', 'value' => $loan->sector, 'icon' => 'bx bx-tag'],
                            ] as $item)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="p-3 bg-light rounded-3 d-flex align-items-center">
                                    <i class="{{ $item['icon'] }} me-3 fs-3 text-primary"></i>
                                    <div>
                                        <p class="text-muted text-uppercase fw-bold mb-0" style="font-size: 0.8rem;">{{ $item['label'] }}</p>
                                        <p class="fw-bold mb-0 text-dark">{{ $item['value'] }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="schedule" role="tabpanel">
                @if($loan->schedule->count())
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary ps-4">Due Date</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Principal</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Interest</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary text-end pe-4">Total Installment</th>
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
                <div class="card card-body text-center p-5">
                    <h4 class="text-muted">No repayment schedule available.</h4>
                    <p class="text-secondary">A schedule will be generated once the loan is approved.</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="guarantors" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Guarantors</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addGuarantorModal">
                        <i class="bx bx-user-plus me-2 font-18"></i>Add Guarantor
                    </button>
                </div>

                @if($loan->guarantors && $loan->guarantors->count())
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary ps-4">#</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Name</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Phone</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary text-end pe-4">Actions</th>
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
                <div class="card card-body text-center p-5">
                    <h4 class="text-muted">No guarantors assigned to this loan.</h4>
                    <p class="text-secondary">Click the button above to add a guarantor.</p>
                </div>
                @endif
            </div>

            <div class="tab-pane fade" id="documents" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-dark">Documents</h5>
                    <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="bx bx-cloud-upload me-2 font-18"></i>Upload Document
                    </button>
                </div>

                @if($loan->loanFiles->count())
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary ps-4">#</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary">Document Name</th>
                                        <th scope="col" class="text-uppercase fw-bold text-secondary text-end pe-4">Actions</th>
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
                <div class="card card-body text-center p-5">
                    <h4 class="text-muted">No documents uploaded yet.</h4>
                    <p class="text-secondary">Click the button above to add the first document.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                    <label for="guarantor_id" class="form-label">File Type</label>
                    <select class="form-select" name="file_type_id" id="file_type_id" required>
                        <option value="">-- Select Document Type --</option>
                        @foreach($filetypes as $file)
                        <option value="{{ $file->id }}">{{ $file->name }}</option>
                        @endforeach
                    </select>
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