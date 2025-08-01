
@extends('layouts.main')
@section('title', 'Create Journal Entry')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Accounting</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('accounting.journals.index') }}">Journal Entries</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create New Entry</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-plus-circle me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">Create New Journal Entry</h5>
                                </div>
                                <p class="mb-0 text-muted">Add a new journal entry with debit and credit transactions</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('accounting.journals.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Journals
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Entry Form -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Journal Entry Details</h6>
                    </div>
            <div class="card-body">
                @include('accounting.journals.form')
                    </div>
                </div>
            </div>
        </div>       
    </div>
</div>
@endsection