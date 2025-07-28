@extends('layouts.main')
@section('title', 'Create Cash Collateral Type')

@section('content')
<div class="page-wrapper">
    <div class="page-content">        
        <h6 class="mb-0 text-uppercase">CREATE NEW CASH COLLATERAL TYPE</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('cash_collateral_types.form')
            </div>
        </div>       
    </div>
</div>
@endsection