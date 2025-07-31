@extends('layouts.main')
@section('title', 'Edit Cash Collateral')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Cash Collaterals', 'url' => route('cash_collaterals.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Edit Cash Collateral', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />        
        <h6 class="mb-0 text-uppercase">EDIT CASH COLLATERAL</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('cash_collaterals.form')
            </div>
        </div>       
    </div>
</div>
@endsection