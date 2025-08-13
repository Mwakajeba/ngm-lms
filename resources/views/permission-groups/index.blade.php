@extends('layouts.main')

@section('title', 'Permission Groups')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <h6 class="mb-0 text-uppercase">PERMISSION GROUPS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Permission Groups</h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="window.location.href='{{ route('roles.index') }}'">
                            <i class="bx bx-arrow-back"></i> Back to Roles
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPermissionGroupModal">
                            <i class="bx bx-plus"></i> Add New Group
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Icon</th>
                                    <th>Name</th>
                                    <th>Display Name</th>
                                    <th>Description</th>
                                    <th>Permissions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissionGroups as $group)
                                    <tr>
                                        <td>
                                            @if($group->icon)
                                                <i class="{{ $group->icon }}" style="color: {{ $group->color }}; font-size: 1.2rem;"></i>
                                            @else
                                                <i class="bx bx-folder" style="color: {{ $group->color }}; font-size: 1.2rem;"></i>
                                            @endif
                                        </td>
                                        <td><code>{{ $group->name }}</code></td>
                                        <td>{{ $group->display_name }}</td>
                                        <td>{{ $group->description ?? 'No description' }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $group->permissions_count }}</span>
                                        </td>
                                        <td>
                                            @if($group->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editPermissionGroup({{ $group->id }})">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            @if($group->permissions_count == 0)
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deletePermissionGroup({{ $group->id }})">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No permission groups found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Permission Group Modal -->
<div class="modal fade" id="createPermissionGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Permission Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createPermissionGroupForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <small class="form-text text-muted">Lowercase, no spaces (e.g., loan_management)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="display_name" class="form-label">Display Name *</label>
                        <input type="text" class="form-control" id="display_name" name="display_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="icon" name="icon" placeholder="e.g., bx bx-user">
                            <button type="button" class="btn btn-outline-secondary" onclick="openIconPicker()">
                                <i class="bx bx-palette"></i> Pick Icon
                            </button>
                        </div>
                        <small class="form-text text-muted">Boxicons class name (e.g., bx bx-user, bx bx-home)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="color" name="color" value="#6c757d">
                    </div>
                    
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Permission Group Modal -->
<div class="modal fade" id="editPermissionGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Permission Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPermissionGroupForm">
                <input type="hidden" id="edit_group_id" name="group_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_display_name" class="form-label">Display Name *</label>
                        <input type="text" class="form-control" id="edit_display_name" name="display_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_icon" class="form-label">Icon</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="edit_icon" name="icon" placeholder="e.g., bx bx-user">
                            <button type="button" class="btn btn-outline-secondary" onclick="openIconPicker('edit')">
                                <i class="bx bx-palette"></i> Pick Icon
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="edit_color" name="color">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="edit_sort_order" name="sort_order" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Icon Picker Modal -->
<div class="modal fade" id="iconPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="iconSearch" placeholder="Search icons...">
                </div>
                <div class="row" id="iconGrid">
                    <!-- Icons will be loaded here -->
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
// Common Boxicons for permission groups
const commonIcons = [
    'bx bx-user', 'bx bx-users', 'bx bx-home', 'bx bx-building', 'bx bx-money', 'bx bx-credit-card',
    'bx bx-wallet', 'bx bx-bank', 'bx bx-calculator', 'bx bx-chart', 'bx bx-pie-chart', 'bx bx-bar-chart',
    'bx bx-file', 'bx bx-folder', 'bx bx-document', 'bx bx-book', 'bx bx-library', 'bx bx-graduation',
    'bx bx-cog', 'bx bx-settings', 'bx bx-shield', 'bx bx-lock', 'bx bx-key', 'bx bx-bell', 'bx bx-envelope',
    'bx bx-phone', 'bx bx-mobile', 'bx bx-desktop', 'bx bx-laptop', 'bx bx-printer', 'bx bx-scan',
    'bx bx-camera', 'bx bx-image', 'bx bx-video', 'bx bx-music', 'bx bx-play', 'bx bx-pause', 'bx bx-stop',
    'bx bx-calendar', 'bx bx-time', 'bx bx-timer', 'bx bx-alarm', 'bx bx-notification', 'bx bx-message',
    'bx bx-chat', 'bx bx-comment', 'bx bx-like', 'bx bx-heart', 'bx bx-star', 'bx bx-bookmark',
    'bx bx-share', 'bx bx-link', 'bx bx-export', 'bx bx-import', 'bx bx-download', 'bx bx-upload',
    'bx bx-sync', 'bx bx-refresh', 'bx bx-reset', 'bx bx-rotate-left', 'bx bx-rotate-right',
    'bx bx-zoom-in', 'bx bx-zoom-out', 'bx bx-search', 'bx bx-filter', 'bx bx-sort', 'bx bx-list',
    'bx bx-grid', 'bx bx-menu', 'bx bx-hamburger', 'bx bx-x', 'bx bx-plus', 'bx bx-minus',
    'bx bx-check', 'bx bx-x-circle', 'bx bx-check-circle', 'bx bx-info-circle', 'bx bx-question-circle',
    'bx bx-exclamation-circle', 'bx bx-warning', 'bx bx-error', 'bx bx-help-circle'
];

let currentIconTarget = 'create';

function openIconPicker(target = 'create') {
    currentIconTarget = target;
    loadIcons();
    $('#iconPickerModal').modal('show');
}

function loadIcons() {
    const iconGrid = $('#iconGrid');
    iconGrid.empty();
    
    commonIcons.forEach(icon => {
        const iconElement = $(`
            <div class="col-2 mb-2">
                <div class="icon-item p-2 text-center border rounded cursor-pointer" 
                     onclick="selectIcon('${icon}')" 
                     style="cursor: pointer; transition: all 0.2s;">
                    <i class="${icon}" style="font-size: 1.5rem;"></i>
                    <div class="small mt-1">${icon}</div>
                </div>
            </div>
        `);
        iconGrid.append(iconElement);
    });
}

function selectIcon(iconClass) {
    if (currentIconTarget === 'edit') {
        $('#edit_icon').val(iconClass);
    } else {
        $('#icon').val(iconClass);
    }
    $('#iconPickerModal').modal('hide');
}

// Search functionality
$('#iconSearch').on('input', function() {
    const searchTerm = $(this).val().toLowerCase();
    $('.icon-item').each(function() {
        const iconText = $(this).text().toLowerCase();
        if (iconText.includes(searchTerm)) {
            $(this).parent().show();
        } else {
            $(this).parent().hide();
        }
    });
});

// Create permission group
$('#createPermissionGroupForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: '{{ route("permission-groups.store") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#createPermissionGroupModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.message) {
                alert('Error: ' + response.message);
            } else {
                alert('An error occurred while creating the permission group.');
            }
        }
    });
});

// Edit permission group
function editPermissionGroup(groupId) {
    $.get(`/permission-groups/${groupId}/edit`, function(data) {
        $('#edit_group_id').val(data.id);
        $('#edit_name').val(data.name);
        $('#edit_display_name').val(data.display_name);
        $('#edit_description').val(data.description);
        $('#edit_icon').val(data.icon);
        $('#edit_color').val(data.color);
        $('#edit_sort_order').val(data.sort_order);
        $('#edit_is_active').prop('checked', data.is_active);
        
        $('#editPermissionGroupModal').modal('show');
    });
}

// Update permission group
$('#editPermissionGroupForm').on('submit', function(e) {
    e.preventDefault();
    
    const groupId = $('#edit_group_id').val();
    const formData = new FormData(this);
    formData.append('_method', 'PUT');
    
    $.ajax({
        url: `/permission-groups/${groupId}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#editPermissionGroupModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.message) {
                alert('Error: ' + response.message);
            } else {
                alert('An error occurred while updating the permission group.');
            }
        }
    });
});

// Delete permission group
function deletePermissionGroup(groupId) {
    if (confirm('Are you sure you want to delete this permission group?')) {
        $.ajax({
            url: `/permission-groups/${groupId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.message) {
                    alert('Error: ' + response.message);
                } else {
                    alert('An error occurred while deleting the permission group.');
                }
            }
        });
    }
}

// Icon item hover effects
$(document).on('mouseenter', '.icon-item', function() {
    $(this).addClass('bg-light');
});

$(document).on('mouseleave', '.icon-item', function() {
    $(this).removeClass('bg-light');
});
</script>
@endpush 