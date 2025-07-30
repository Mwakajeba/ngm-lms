@extends('layouts.main')

@section('title', 'Edit Journal')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">EDIT JOURNAL</h6>
                </div>
                <div>
                    <a href="{{ route('accounting.journals.index') }}" class="btn btn-secondary">
                        Back to Journals
                    </a>
                </div>
            </div>
            <hr />

            @include('accounting.journals.form')
        </div>
    </div>
@endsection