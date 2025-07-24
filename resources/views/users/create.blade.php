@extends('layouts.main')
@section('title', 'Create')

@section('content')
<div class="page-wrapper">
    <div class="page-content">        
        <h6 class="mb-0 text-uppercase">CREATE NEW USER</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('users.form')
            </div>
        </div>       
    </div>
</div>
@endsection