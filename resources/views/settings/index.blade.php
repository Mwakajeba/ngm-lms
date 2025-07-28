@extends('layouts.main')

@section('title', 'Settings')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">SETTINGS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">System Settings</h4>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(isset($errors) && $errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    Please fix the following errors:
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="row">
                                <!-- Company Settings -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-building fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Company Settings</h5>
                                            <p class="card-text">Manage your company information and preferences.</p>
                                            <a href="{{ route('settings.company') }}" class="btn btn-primary">
                                                <i class="bx bx-cog me-1"></i> Configure
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Branch Settings -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-git-branch fs-1 text-success"></i>
                                            </div>
                                            <h5 class="card-title">Branch Settings</h5>
                                            <p class="card-text">Manage branches and their configurations.</p>
                                            <a href="{{ route('settings.branches') }}" class="btn btn-success">
                                                <i class="bx bx-cog me-1"></i> Configure
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- User Settings -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-user fs-1 text-info"></i>
                                            </div>
                                            <h5 class="card-title">User Settings</h5>
                                            <p class="card-text">Manage user preferences and permissions.</p>
                                            <a href="{{ route('settings.user') }}" class="btn btn-info">
                                                <i class="bx bx-cog me-1"></i> Configure
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- System Settings -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-cog fs-1 text-warning"></i>
                                            </div>
                                            <h5 class="card-title">System Settings</h5>
                                            <p class="card-text">Configure system-wide settings and preferences.</p>
                                            <a href="{{ route('settings.system') }}" class="btn btn-warning">
                                                <i class="bx bx-cog me-1"></i> Configure
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Backup Settings -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-data fs-1 text-danger"></i>
                                            </div>
                                            <h5 class="card-title">Backup Settings</h5>
                                            <p class="card-text">Manage data backup and restore operations.</p>
                                            <a href="{{ route('settings.backup') }}" class="btn btn-danger">
                                                <i class="bx bx-cog me-1"></i> Configure
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- AI Assistant -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-purple">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-bot fs-1 text-purple"></i>
                                            </div>
                                            <h5 class="card-title">AI Assistant</h5>
                                            <p class="card-text">Get intelligent reports and insights with AI-powered
                                                analysis.</p>
                                            <a href="{{ route('settings.ai') }}" class="btn btn-purple">
                                                <i class="bx bx-bot me-1"></i> Start Chat
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Roles & Permissions -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-secondary">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-shield fs-1 text-secondary"></i>
                                            </div>
                                            <h5 class="card-title">Roles & Permissions</h5>
                                            <p class="card-text">Manage user roles, permissions, and access control.</p>
                                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-shield me-1"></i> Manage
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-globe fs-1 text-info"></i>
                                            </div>
                                            <h5 class="card-title">Language Test</h5>
                                            <p class="card-text">Test the multi-language functionality of the system.</p>
                                            <a href="{{ route('test.language') }}" class="btn btn-info">
                                                <i class="bx bx-globe me-1"></i> Test Language
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Penalty Settings -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-orange">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-time fs-1 text-orange"></i>
                                            </div>
                                            <h5 class="card-title">Penalty Settings</h5>
                                            <p class="card-text">Configure late payment penalties and fee structures.</p>
                                            <a href="{{ route('settings.penalty') }}" class="btn btn-orange">
                                                <i class="bx bx-cog me-1"></i> Configure
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fees Settings -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-teal">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-dollar-circle fs-1 text-teal"></i>
                                            </div>
                                            <h5 class="card-title">Fees Settings</h5>
                                            <p class="card-text">Manage service fees, charges, and payment structures.</p>
                                            <a href="{{ route('settings.fees') }}" class="btn btn-teal">
                                                <i class="bx bx-cog me-1"></i> Configure
                                            </a>
                                        </div>
                                    </div>
                                </div>

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

@push('styles')
    <style>
        .border-purple {
            border-color: #6f42c1 !important;
        }

        .text-purple {
            color: #6f42c1 !important;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }

        .btn-purple:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
            color: white;
        }

        .border-orange {
            border-color: #fd7e14 !important;
        }

        .text-orange {
            color: #fd7e14 !important;
        }

        .btn-orange {
            background-color: #fd7e14;
            border-color: #fd7e14;
            color: white;
        }

        .btn-orange:hover {
            background-color: #e8690b;
            border-color: #e8690b;
            color: white;
        }

        .border-teal {
            border-color: #20c997 !important;
        }

        .text-teal {
            color: #20c997 !important;
        }

        .btn-teal {
            background-color: #20c997;
            border-color: #20c997;
            color: white;
        }

        .btn-teal:hover {
            background-color: #1ba37e;
            border-color: #1ba37e;
            color: white;
        }
    </style>
@endpush