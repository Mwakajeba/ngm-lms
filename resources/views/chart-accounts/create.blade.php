@extends('layouts.main')
@section('title', 'Create Chart Account')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">CREATE NEW CHART ACCOUNT</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    @include('chart-accounts.form')
                </div>
            </div>
        </div>
    </div>
@endsection