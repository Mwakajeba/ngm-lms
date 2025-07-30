
@extends('layouts.main')
@section('title', 'Create Journal')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <h6 class="mb-0 text-uppercase">CREATE NEW JOURNAL</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('accounting.journals.form')
            </div>
        </div>       
    </div>
</div>
@endsection