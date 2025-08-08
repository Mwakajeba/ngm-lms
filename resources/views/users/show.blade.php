@extends('layouts.main')

@section('title', 'User Profile')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'User Management', 'url' => route('users.index'), 'icon' => 'bx bx-user'],
            ['label' => $user->name, 'url' => '#', 'icon' => 'bx bx-user-circle']
        ]" />

            <h6 class="mb-0 text-uppercase">USER PROFILE</h6>
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
                                                <td>{{ $user->user_id }}</td>
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

                            <hr class="my-4">

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                @can('edit user')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                    <i class="bx bx-edit me-1"></i> Edit User
                                </a>
                                @endcan

                                @if($user->status === 'active')
                                    @can('edit user')
                                    <form action="{{ route('users.status', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="bx bx-pause-circle me-1"></i> Deactivate
                                        </button>
                                    </form>
                                    @endcan
                                @else
                                    @can('edit user')
                                    <form action="{{ route('users.status', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bx bx-play-circle me-1"></i> Activate
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                @can('delete user')
                                    @if($user->id !== auth()->id())
                                    <button type="button" class="btn btn-danger w-100 delete-user-btn" 
                                            data-user-id="{{ $user->id }}" 
                                            data-user-name="{{ $user->name }}">
                                        <i class="bx bx-trash me-1"></i> Delete User
                                    </button>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Details -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">User Information</h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Full Name</label>
                                        <p class="text-muted mb-0">{{ $user->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <p class="text-muted mb-0">{{ $user->email ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <p class="text-muted mb-0">{{ $user->phone }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Branch</label>
                                        <p class="text-muted mb-0">{{ $user->branch->name ?? 'Not assigned' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status</label>
                                        <p class="mb-0">
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Account Created</label>
                                        <p class="text-muted mb-0">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roles & Permissions -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Roles & Permissions</h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-3">Assigned Roles</h6>
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-primary me-2">{{ $role->name }}</span>
                                                <small class="text-muted">{{ $role->permissions->count() }} permissions</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No roles assigned</p>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <h6 class="mb-3">Direct Permissions</h6>
                                    @if($user->permissions->count() > 0)
                                        @foreach($user->permissions as $permission)
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-info me-2">{{ $permission->name }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No direct permissions</p>
                                    @endif
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

@push('scripts')
<script>
$(function() {
    // Initialize any necessary scripts
});

// Delete user functionality with SweetAlert
$(document).on('click', '.delete-user-btn', function(e) {
    e.preventDefault();
    const userId = $(this).data('user-id');
    const userName = $(this).data('user-name');
    
    Swal.fire({
        title: 'Delete User',
        text: `Are you sure you want to delete user "${userName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitDeleteUserForm(userId);
        }
    });
});

// Helper function to submit delete user form
function submitDeleteUserForm(userId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/users/${userId}`;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add method override
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    // Submit the form
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush 