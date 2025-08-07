@extends('layouts.main')

@section('title', 'Roles & Permissions Management')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">ROLES & PERMISSIONS</h6>
            <hr />
            <!-- Statistics Cards -->
            <div class="row row-cols-1 row-cols-lg-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium mb-1">Total Roles</p>
                                    <h4 class="mb-0">{{ $roles->count() }}</h4>
                                </div>
                                <div class="ms-3">
                                    <div
                                        class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-shield font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium mb-1">Total Permissions</p>
                                    <h4 class="mb-0">{{ $permissions->count() }}</h4>
                                </div>
                                <div class="ms-3">
                                    <div
                                        class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-key font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium mb-1">Active Users</p>
                                    <h4 class="mb-0">{{ $activeUsers }}</h4>
                                </div>
                                <div class="ms-3">
                                    <div
                                        class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-user font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium mb-1">System Roles</p>
                                    <h4 class="mb-0">{{ $systemRoles }}</h4>
                                </div>
                                <div class="ms-3">
                                    <div
                                        class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-cog font-size-24"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Roles Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">Roles List</h4>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#createPermissionModal">
                                        <i class="bx bx-key"></i> Create Permission
                                    </button>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#createRoleModal">
                                        <i class="bx bx-plus"></i> Create New Role
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap" id="rolesTable">
                                    <thead>
                                        <tr>
                                            <th>Role Name</th>
                                            <th>Description</th>
                                            <th>Permissions</th>
                                            <th>Users</th>
                                            <th>Type</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roles as $role)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-3">
                                                            <div class="avatar-title bg-primary rounded-circle">
                                                                <i class="bx bx-shield"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">{{ ucfirst($role->name) }}</h6>
                                                            <small class="text-muted">{{ $role->guard_name }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-muted">
                                                        {{ $role->description ?? 'No description available' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($role->permissions->take(3) as $permission)
                                                            <span class="badge bg-light text-dark">{{ $permission->name }}</span>
                                                        @endforeach
                                                        @if($role->permissions->count() > 3)
                                                            <span class="badge bg-secondary">+{{ $role->permissions->count() - 3 }}
                                                                more</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $role->users->count() }} users</span>
                                                </td>
                                                <td>
                                                    @if(in_array($role->name, ['super-admin', 'admin', 'manager', 'user', 'viewer']))
                                                        <span class="badge bg-warning">System</span>
                                                    @else
                                                        <span class="badge bg-success">Custom</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-info me-1"
                                                        onclick="viewRole({{ $role->id }})" title="View"><i
                                                            class="bx bx-show"></i></button>
                                                    <button class="btn btn-sm btn-outline-primary me-1"
                                                        onclick="editRole({{ $role->id }})" title="Edit"><i
                                                            class="bx bx-edit"></i></button>
                                                    <a href="{{ route('roles.menus', $role) }}"
                                                        class="btn btn-sm btn-outline-success me-1" title="Manage Menus"><i
                                                            class="bx bx-menu"></i></a>
                                                    @if(!in_array($role->name, ['super-admin', 'admin']))
                                                        <button class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteRole({{ $role->id }})" title="Delete"><i
                                                                class="bx bx-trash"></i></button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Role Modal -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createRoleModalLabel"><i class="bx bx-plus me-2"></i> Create New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createRoleForm" action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <p class="text-muted">Fill in the details below to create a new role. Assign permissions as
                                needed.</p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="roleName" class="form-label">Role Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="roleName" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="roleGuard" class="form-label">Guard</label>
                                    <select class="form-select" id="roleGuard" name="guard_name">
                                        <option value="web" selected>Web</option>
                                        <option value="api">API</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="roleDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="roleDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="row">
                                @foreach($permissionGroups as $group => $permissions)
                                    <div class="col-md-4 mb-3">
                                        <div class="card border">
                                            <div class="card-header bg-light py-2">
                                                <h6 class="mb-0">{{ ucfirst($group) }}</h6>
                                            </div>
                                            <div class="card-body py-2" style="max-height: 200px; overflow-y: auto;">
                                                @foreach($permissions as $permission)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                                            value="{{ $permission->id }}" id="perm_{{ $permission->id }}">
                                                        <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                                            {{ ucwords(str_replace(['-', '_'], ' ', $permission->name)) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editRoleForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" id="editRoleModalBody">
                        <!-- Content will be loaded dynamically -->
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Create Permission Modal -->
    <div class="modal fade" id="createPermissionModal" tabindex="-1" aria-labelledby="createPermissionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="createPermissionModalLabel"><i class="bx bx-key me-2"></i> Create New
                        Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createPermissionForm" action="{{ route('permissions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <p class="text-muted">Create a new permission for the system.</p>
                        </div>
                        <div class="mb-3">
                            <label for="permissionName" class="form-label">Permission Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="permissionName" name="name"
                                placeholder="e.g., create-loans, view-reports" required>
                            <small class="text-muted">Use lowercase with hyphens (e.g., create-loans, view-reports)</small>
                        </div>
                        <div class="mb-3">
                            <label for="permissionGuard" class="form-label">Guard</label>
                            <select class="form-select" id="permissionGuard" name="guard_name">
                                <option value="web" selected>Web</option>
                                <option value="api">API</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="permissionGroup" class="form-label">Permission Group</label>
                            <select class="form-select" id="permissionGroup" name="group">
                                <option value="user">User Management</option>
                                <option value="client">Client Management</option>
                                <option value="loan">Loan Management</option>
                                <option value="borrower">Borrower Management</option>
                                <option value="collection">Collections & Payments</option>
                                <option value="accounting">Accounting & Financial</option>
                                <option value="savings">Savings & Deposits</option>
                                <option value="report">Reports & Analytics</option>
                                <option value="risk">Risk Management</option>
                                <option value="settings">Settings & Configuration</option>
                                <option value="ai">AI Assistant</option>
                                <option value="dashboard">Dashboard & Analytics</option>
                                <option value="menu">Menu Management</option>
                            </select>
                            <small class="text-muted">Select the category this permission belongs to</small>
                        </div>
                        <div class="mb-3">
                            <label for="permissionDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="permissionDescription" name="description" rows="3"
                                placeholder="Describe what this permission allows users to do"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Role Modal -->
    <div class="modal fade" id="viewRoleModal" tabindex="-1" aria-labelledby="viewRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewRoleModalLabel">Role Details</h5>
                </div>
                <div class="modal-body" id="viewRoleModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#rolesTable').DataTable({
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function (row) {
                                var data = row.data();
                                return 'Details for ' + data[0];
                            }
                        }),
                        renderer: $.fn.dataTable.Responsive.renderer.tableAll()
                    }
                },
                order: [[0, 'asc']],
                pageLength: 10,
                language: {
                    search: "",
                    searchPlaceholder: "Search roles..."
                },
                columnDefs: [
                    {
                        targets: -1, // Actions column (last column)
                        responsivePriority: 1, // Highest priority - never hide
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: [0, 1], // Role Name and Description
                        responsivePriority: 2
                    },
                    {
                        targets: [2, 3, 4], // Permissions, Users, Type
                        responsivePriority: 3
                    }
                ]
            });

            // DataTable is now properly configured with responsive behavior
            // Actions column will always be visible

            // Handle role creation form submission
            $('#createRoleForm').on('submit', function (e) {
                e.preventDefault();

                const form = this;
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Creating...';
                }

                const formData = new FormData(form);

                $.ajax({
                    url: form.action,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        Swal.fire('{{ __("app.success") }}!', response.message || '{{ __("app.role_created_successfully") }}', 'success')
                            .then(() => {
                                $('#createRoleModal').modal('hide');
                                location.reload();
                            });
                    },
                    error: function (xhr, status, error) {
                        let errorMessage = '{{ __("app.failed_to_create_role") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            // Handle validation errors
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('\n');
                        }
                        Swal.fire('{{ __("app.error") }}!', errorMessage, 'error');
                    },
                    complete: function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }
                });
            });

            // Handle permission creation form submission
            $('#createPermissionForm').on('submit', function (e) {
                e.preventDefault();

                const form = this;
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Creating...';
                }

                const formData = new FormData(form);

                $.ajax({
                    url: form.action,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        Swal.fire('{{ __("app.success") }}!', '{{ __("app.permission_created_successfully") }}', 'success')
                            .then(() => {
                                $('#createPermissionModal').modal('hide');
                                location.reload();
                            });
                    },
                    error: function (xhr) {
                        let errorMessage = '{{ __("app.failed_to_create_permission") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire('{{ __("app.error") }}!', errorMessage, 'error');
                    },
                    complete: function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }
                });
            });
        });

        function editRole(roleId) {
            $.get(`/roles/${roleId}/edit`, function (data) {
                $('#editRoleModalBody').html(data);
                $('#editRoleForm').attr('action', `/roles/${roleId}`);
                $('#editRoleModal').modal('show');

                // Add form submission handler with double submit prevention
                $('#editRoleForm').off('submit').on('submit', function (e) {
                    e.preventDefault();

                    const form = this;
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn ? submitBtn.innerHTML : '';

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Updating...';
                    }

                    // Manually collect form data to ensure all fields are included
                    const formData = new FormData();

                    // Add CSRF token
                    const csrfToken = form.querySelector('input[name="_token"]');
                    if (csrfToken) {
                        formData.append('_token', csrfToken.value);
                    }

                    // Add name field
                    const nameField = form.querySelector('input[name="name"]');
                    if (nameField) {
                        formData.append('name', nameField.value);
                    }

                    // Add description field
                    const descField = form.querySelector('textarea[name="description"]');
                    if (descField) {
                        formData.append('description', descField.value);
                    }

                    // Add guard_name field
                    const guardField = form.querySelector('select[name="guard_name"]');
                    if (guardField) {
                        formData.append('guard_name', guardField.value);
                    }

                    // Add permissions
                    const permissionCheckboxes = form.querySelectorAll('input[name="permissions[]"]:checked');
                    permissionCheckboxes.forEach(checkbox => {
                        formData.append('permissions[]', checkbox.value);
                    });

                    $.ajax({
                        url: form.action,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire('{{ __("app.success") }}!', response.message || '{{ __("app.role_updated_successfully") }}', 'success')
                                    .then(() => {
                                        $('#editRoleModal').modal('hide');
                                        location.reload();
                                    });
                            } else {
                                Swal.fire('{{ __("app.error") }}!', response.message || '{{ __("app.failed_to_update_role") }}', 'error');
                            }
                        },
                        error: function (xhr, status, error) {
                            let errorMessage = '{{ __("app.failed_to_update_role") }}';

                            try {
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    // Handle validation errors
                                    const errors = xhr.responseJSON.errors;
                                    errorMessage = Object.values(errors).flat().join('\n');
                                } else if (xhr.responseText) {
                                    // Try to parse response as JSON
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    }
                                }
                            } catch (e) {
                                errorMessage = 'Server error: ' + xhr.status + ' - ' + xhr.statusText;
                            }

                            Swal.fire('{{ __("app.error") }}!', errorMessage, 'error');
                        },
                        complete: function () {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                            }
                        }
                    });

                    return false;
                });
            });
        }



        function viewRole(roleId) {
            $.get(`/roles/${roleId}`, function (data) {
                $('#viewRoleModalBody').html(data);
                $('#viewRoleModal').modal('show');
            });
        }

        function deleteRole(roleId) {
            Swal.fire({
                title: '{{ __("app.confirm") }}',
                text: "{{ __('app.are_you_sure_delete_role') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("app.yes") }}',
                cancelButtonText: '{{ __("app.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/roles/${roleId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.fire('{{ __("app.deleted") }}!', '{{ __("app.role_deleted_successfully") }}', 'success')
                                .then(() => {
                                    location.reload();
                                });
                        },
                        error: function (xhr) {
                            Swal.fire('{{ __("app.error") }}!', '{{ __("app.failed_to_delete_role") }}', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush