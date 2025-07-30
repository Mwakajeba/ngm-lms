@extends('layouts.main')
@section('title', 'Create Customer')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <div class="row">
                <div class="col-12">
                    <div class="page-breadcrumb d-flex align-items-center">
                        <div class="me-auto">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                                <li class="breadcrumb-item active">Create Customer</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <h6 class="mb-0 text-uppercase">CREATE NEW CUSTOMER</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('customers.form')
            </div>
        </div>       
    </div>
</div>
@endsection