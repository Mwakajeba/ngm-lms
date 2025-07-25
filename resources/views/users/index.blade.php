@extends('layouts.main')

@section('title', 'User Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="row row-cols-1 row-cols-lg-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Users</p>
                                <h4 class="font-weight-bold">{{ $totalUsers }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-user'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Active Users</p>
                                <h4 class="font-weight-bold">{{ $activeUsers }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-check-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Inactive Users</p>
                                <h4 class="font-weight-bold">{{ $inactiveUsers }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-lush text-white"><i class='bx bx-time'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">This Month</p>
                                <h4 class="font-weight-bold">{{ $users->where('created_at', '>=', now()->startOfMonth())->count() }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-kyoto text-white"><i class='bx bx-calendar'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
        
        <h6 class="mb-0 text-uppercase">USERS</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Users List</h5>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Add New User
                    </a>
                </div>
                

                <div class="table-responsive">
                    <table id="example2" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                             <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Branch</th>
                                <th>Roles</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                          @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-primary rounded-circle me-2">
                                            <span class="avatar-title text-white">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold">
                                                <a href="{{ route('users.profile') }}" class="text-decoration-none">{{ $user->name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->branch->name ?? 'N/A' }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($user->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($user->status === 'inactive')
                                        <span class="badge bg-warning">Inactive</span>
                                    @else
                                        <span class="badge bg-danger">Suspended</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('users.profile') }}" class="btn btn-sm btn-info">Profile</a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline-block;" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" data-name="{{ $user->name }}">Delete</button>
                                    </form>
                                </td>
                            </tr>
                          @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                <div class="row mt-4">
                    <div class="col-12">
                        {{ $users->links() }}
                    </div>
                </div>
                @endif
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

<!-- Delete User Form -->
<form id="deleteUserForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
function deleteUser(userHashId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        const form = document.getElementById('deleteUserForm');
        form.action = `/users/${userHashId}`;
        form.submit();
    }
}

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Status filter
document.getElementById('statusFilter').addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const statusCell = row.querySelector('td:nth-child(6)');
        if (statusCell) {
            const userStatus = statusCell.textContent.toLowerCase();
            row.style.display = !status || userStatus.includes(status) ? '' : 'none';
        }
    });
});

// Role filter
document.getElementById('roleFilter').addEventListener('change', function() {
    const role = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const roleCell = row.querySelector('td:nth-child(5)');
        if (roleCell) {
            const userRoles = roleCell.textContent.toLowerCase();
            row.style.display = !role || userRoles.includes(role) ? '' : 'none';
        }
    });
});
</script>
@endpush