@extends('layouts.main')

@section('title', 'Cash Collateral Types')

@section('content')
<div class="container py-4">
    <h2>Cash Collateral Types</h2>
    <hr>

    <a href="{{ route('cash_collateral_types.create') }}" class="btn btn-primary mb-3">Create New</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Chart Account</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashCollaterals as $type)
                <tr>
                    <td>{{ $type->name }}</td>
                    <td>{{ $type->chartAccount ? $type->chartAccount->name : 'N/A' }}</td>
                    <td>{{ $type->description ?? '-' }}</td>
                    <td>
                        @if($type->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $type->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('cash_collateral_types.edit', $type) }}" class="btn btn-sm btn-warning">Edit</a>
                        
                         <form action="{{ route('cash_collateral_types.destroy', $type) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" data-name="{{ $type->name }}">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No cash collateral types found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $cashCollaterals->links() }}
</div>
@endsection
