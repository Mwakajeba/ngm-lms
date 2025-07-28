@extends('layouts.main')
@section('title', 'Create Account Class Group')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">CREATE NEW ACCOUNT CLASS GROUP</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    @include('account-class-groups.form')
                </div>
            </div>
        </div>
    </div>
@endsection