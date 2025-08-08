@extends('layouts.main')

@section('title', 'User Profile - ' . $user->name)

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'User Management', 'url' => route('users.index'), 'icon' => 'bx bx-user'],
            ['label' => $user->name, 'url' => '#', 'icon' => 'bx bx-user-circle']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">USER PROFILE</h6>
                <div>
                    @can('edit user')
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-edit me-1"></i> Edit User
                        </a>
                    @endcan
                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Back to Users
                    </a>
                </div>
            </div>
            <hr />

            <div class="row">
                <!-- Profile Card -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="avatar-lg mx-auto mb-4">
                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle font-size-24">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                </div>
                                <h5 class="font-size-16 mb-1 text-truncate">{{ $user->name }}</h5>
                                <p class="text-muted text-truncate mb-3">{{ $user->email ?? 'No email' }}</p>

                                <!-- Status Badge -->
                                @if($user->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($user->status === 'inactive')
                                    <span class="badge bg-warning">Inactive</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </div>

                            <hr class="my-4">

                            <div class="text-muted">
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">User ID :</th>
                                                <td>{{ $user->user_id ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Phone :</th>
                                                <td>{{ $user->phone }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Branch :</th>
                                                <td>{{ $user->branch->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Company :</th>
                                                <td>{{ $user->company->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Joined :</th>
                                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Last Updated :</th>
                                                <td>{{ $user->updated_at->format('M d, Y') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">User Information</h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Full Name</label>
                                        <p class="form-control-plaintext">{{ $user->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <p class="form-control-plaintext">{{ $user->email ?? 'No email provided' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <p class="form-control-plaintext">{{ $user->phone }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status</label>
                                        <p class="form-control-plaintext">
                                            @if($user->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($user->status === 'inactive')
                                                <span class="badge bg-warning">Inactive</span>
                                            @else
                                                <span class="badge bg-danger">Suspended</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Branch</label>
                                        <p class="form-control-plaintext">{{ $user->branch->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Company</label>
                                        <p class="form-control-plaintext">{{ $user->company->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Roles</label>
                                        <p class="form-control-plaintext">
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                            @endforeach
                                            @if($user->roles->isEmpty())
                                                <span class="text-muted">No roles assigned</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Account Created</label>
                                        <p class="form-control-plaintext">
                                            {{ $user->created_at->format('M d, Y \a\t g:i A') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Last Updated</label>
                                        <p class="form-control-plaintext">
                                            {{ $user->updated_at->format('M d, Y \a\t g:i A') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Last Login</label>
                                        <p class="form-control-plaintext">
                                            @if($user->last_login_at)
                                                {{ $user->last_login_at->format('M d, Y \a\t g:i A') }}
                                            @else
                                                <span class="text-muted">Never logged in</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Actions</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                @can('edit user')
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                        <i class="bx bx-edit me-1"></i> Edit User
                                    </a>
                                @endcan

                                @can('delete user')
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                            style="display:inline-block;" class="delete-form"
                                            onsubmit="return confirmDelete(this, 'Are you sure you want to delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-trash me-1"></i> Delete User
                                            </button>
                                        </form>
                                    @endif
                                @endcan

                                @can('change user status')
                                    <form action="{{ route('users.status', $user) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn btn-{{ $user->status === 'active' ? 'warning' : 'success' }}">
                                            <i class="bx bx-{{ $user->status === 'active' ? 'pause' : 'play' }} me-1"></i>
                                            {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete(form, message) {
            if (confirm(message)) {
                form.submit();
            }
            return false;
        }
    </script>
@endpush