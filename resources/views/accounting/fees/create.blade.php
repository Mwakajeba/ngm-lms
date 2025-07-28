@extends('layouts.main')

@section('title', 'Add New Fee')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">ADD NEW FEE</h6>
                    <p class="text-muted mb-0">Create a new fee record</p>
                </div>
                <div>
                    <a href="{{ route('accounting.fees.index') }}" class="btn btn-secondary">
                        Back to Fees
                    </a>
                </div>
            </div>
            <hr />

            @include('accounting.fees.form')
        </div>
    </div>
@endsection