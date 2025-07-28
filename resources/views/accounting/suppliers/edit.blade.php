@extends('layouts.main')

@section('title', 'Edit Supplier')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">EDIT SUPPLIER</h6>
                    <p class="text-muted mb-0">Update supplier information</p>
                </div>
                <div>
                    <a href="{{ route('accounting.suppliers.index') }}" class="btn btn-secondary">
                        Back to Suppliers
                    </a>
                </div>
            </div>
            <hr />

            @include('accounting.suppliers.form')
        </div>
    </div>
@endsection