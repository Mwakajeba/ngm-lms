@extends('layouts.main')
@section('title', 'Create Customer')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Customers', 'url' => route('customers.index')],
            ['label' => 'Create Customer']
        ]" />
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