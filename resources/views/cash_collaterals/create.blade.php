@extends('layouts.main')
@section('title', 'Create Cash Collateral')

@section('content')
<div class="page-wrapper">
    <div class="page-content">        
        <h6 class="mb-0 text-uppercase">CREATE NEW CASH COLLATERAL</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('cash_collaterals.form')
            </div>
        </div>       
    </div>
</div>
@endsection