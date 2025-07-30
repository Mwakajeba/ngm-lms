@extends('layouts.main')
@section('title', 'Journals')

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">JOURNAL</h4>
            <a href="{{ route('accounting.journals.create') }}" class="btn btn-warning">
                <i class="bx bx-plus-circle"></i> Create New
            </a>
        </div>

        <div class="card radius-10">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="journalsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Narration</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Branch</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journals as $i => $journal)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $journal->date->format('Y-m-d') }}</td>
                                    <td>{{ $journal->narration }}</td>
                                    <td>{{ number_format($journal->debit, 2) }}</td>
                                    <td>{{ number_format($journal->credit, 2) }}</td>
                                    <td>{{ $journal->branch->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('accounting.journals.show', $journal) }}" class="btn btn-sm btn-outline-success" title="View">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('accounting.journals.edit', $journal) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('accounting.journals.destroy', $journal) }}" method="POST" class="d-inline-block delete-form" onsubmit="return confirm('Delete this journal entry?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#journalsTable').DataTable({
            responsive: true,
            order: [[1, 'desc']],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search journals..."
            },
            columnDefs: [
                { targets: -1, orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
