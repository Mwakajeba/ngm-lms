@extends('layouts.main')
@section('title', 'Edit Cash Collateral Type')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Cash Collateral Types', 'url' => route('cash_collateral_types.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Edit Cash Collateral Type', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />        
        <h6 class="mb-0 text-uppercase">EDIT CASH COLLATERAL TYPE</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('cash_collateral_types.form')
            </div>
        </div>       
    </div>
</div>
@endsection