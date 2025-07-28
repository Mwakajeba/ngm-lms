@extends('layouts.main')

@section('title', 'Edit Fee')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">EDIT FEE</h6>
                    <p class="text-muted mb-0">Update fee information</p>
                </div>
                <div>
                    <a href="{{ route('accounting.fees.index') }}" class="btn btn-secondary">
                        Back to Fees
                    </a>
                </div>
            </div>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Edit Fee: {{ $fee->name }}</h4>

                            @if(isset($errors) && $errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    Please fix the following errors:
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @include('accounting.fees.form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection