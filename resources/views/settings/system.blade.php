@extends('layouts.main')

@section('title', 'System Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <h6 class="mb-0 text-uppercase">SYSTEM SETTINGS</h6>
        <hr/>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">System Configuration</h4>
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            System settings configuration will be implemented here.
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
<!--start overlay-->
<div class="overlay toggle-icon"></div>
<!--end overlay-->
<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<!--End Back To Top Button-->
<footer class="page-footer">
    <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
</footer>

@endsection 