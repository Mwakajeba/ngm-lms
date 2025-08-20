@extends('layouts.main')
@section('title', 'Edit Cash Collateral Type')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Deposit Accounts Types', 'url' => route('cash_collateral_types.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Edit Deposit Accounts Type', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />        
        <h6 class="mb-0 text-uppercase">EDIT DEPOSIT ACCOUNTS TYPE</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('cash_collateral_types.form')
            </div>
        </div>       
    </div>
</div>
@endsection