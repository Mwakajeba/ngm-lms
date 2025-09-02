@extends('layouts.main')

@section('title', 'Payment Voucher Approval Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Payment Voucher Approval', 'url' => '#', 'icon' => 'bx bx-check-shield']
        ]" />
        <h6 class="mb-0 text-uppercase">PAYMENT VOUCHER APPROVAL SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @can('manage payment voucher approval')
                        <h4 class="card-title mb-4">Payment Voucher Approval Configuration</h4>

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

                        <form action="{{ route('settings.payment-voucher-approval.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Approval Levels -->
                                <div class="col-md-6 mb-3">
                                    <label for="approval_levels" class="form-label">Number of Approval Levels</label>
                                    <select class="form-select" id="approval_levels" name="approval_levels" required>
                                        <option value="">Select Levels</option>
                                        <option value="1" {{ old('approval_levels', $settings->approval_levels ?? 2) == '1' ? 'selected' : '' }}>1 Level</option>
                                        <option value="2" {{ old('approval_levels', $settings->approval_levels ?? 2) == '2' ? 'selected' : '' }}>2 Levels</option>
                                        <option value="3" {{ old('approval_levels', $settings->approval_levels ?? 2) == '3' ? 'selected' : '' }}>3 Levels</option>
                                        <option value="4" {{ old('approval_levels', $settings->approval_levels ?? 2) == '4' ? 'selected' : '' }}>4 Levels</option>
                                        <option value="5" {{ old('approval_levels', $settings->approval_levels ?? 2) == '5' ? 'selected' : '' }}>5 Levels</option>
                                    </select>
                                </div>

                                <!-- Auto Approval Limit -->
                                <div class="col-md-6 mb-3">
                                    <label for="auto_approval_limit" class="form-label">Auto Approval Limit (TZS)</label>
                                    <input type="number" class="form-control" id="auto_approval_limit" name="auto_approval_limit" value="{{ old('auto_approval_limit', $settings->auto_approval_limit ?? 100000) }}" step="1000" min="0" required>
                                    <small class="form-text text-muted">Amount in Tanzania Shillings below which no approval is required</small>
                                </div>

                                <!-- Approval Thresholds -->
                                <div class="col-12 mb-3">
                                    <h6 class="mb-3">Approval Thresholds (TZS)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label for="approval_threshold_1" class="form-label">Level 1 Threshold (TZS)</label>
                                            <input type="number" class="form-control" id="approval_threshold_1" name="approval_threshold_1" value="{{ old('approval_threshold_1', $settings->approval_threshold_1 ?? 500000) }}" step="1000" min="0" required>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for="approval_threshold_2" class="form-label">Level 2 Threshold (TZS)</label>
                                            <input type="number" class="form-control" id="approval_threshold_2" name="approval_threshold_2" value="{{ old('approval_threshold_2', $settings->approval_threshold_2 ?? 2500000) }}" step="1000" min="0">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for="approval_threshold_3" class="form-label">Level 3 Threshold (TZS)</label>
                                            <input type="number" class="form-control" id="approval_threshold_3" name="approval_threshold_3" value="{{ old('approval_threshold_3', $settings->approval_threshold_3 ?? 10000000) }}" step="1000" min="0">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for="approval_threshold_4" class="form-label">Level 4 Threshold (TZS)</label>
                                            <input type="number" class="form-control" id="approval_threshold_4" name="approval_threshold_4" value="{{ old('approval_threshold_4', $settings->approval_threshold_4 ?? 50000000) }}" step="1000" min="0">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for="approval_threshold_5" class="form-label">Level 5 Threshold (TZS)</label>
                                            <input type="number" class="form-control" id="approval_threshold_5" name="approval_threshold_5" value="{{ old('approval_threshold_5', $settings->approval_threshold_5 ?? 100000000) }}" step="1000" min="0">
                                        </div>
                                    </div>
                                </div>

                                <!-- Escalation Time -->
                                <div class="col-md-6 mb-3">
                                    <label for="escalation_time" class="form-label">Escalation Time (Hours)</label>
                                    <input type="number" class="form-control" id="escalation_time" name="escalation_time" value="{{ old('escalation_time', $settings->escalation_time ?? 24) }}" min="1" max="72" required>
                                    <small class="form-text text-muted">Time before approval is escalated to next level</small>
                                </div>

                                <!-- Require Approval for All -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="require_approval_for_all" name="require_approval_for_all" value="1" {{ old('require_approval_for_all', $settings->require_approval_for_all ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="require_approval_for_all">
                                            Require Approval for All Vouchers
                                        </label>
                                        <small class="form-text text-muted d-block">If checked, all vouchers require approval regardless of amount</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Assignments -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="mb-3">Approval Assignments</h6>
                                    <p class="text-muted">Assign roles or specific users to each approval level</p>
                                    
                                    <!-- Level 1 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 1 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level1_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level1_approval_type" name="level1_approval_type">
                                                        <option value="role" {{ old('level1_approval_type', $settings->level1_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level1_approval_type', $settings->level1_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level1_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level1_approvers" name="level1_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                <option value="role_{{ $role->name }}" {{ in_array('role_' . $role->name, old('level1_approvers', $settings->level1_approvers ?? ['role_manager'])) ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                <option value="user_{{ $user->id }}" {{ in_array('user_' . $user->id, old('level1_approvers', $settings->level1_approvers ?? [])) ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Level 2 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 2 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level2_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level2_approval_type" name="level2_approval_type">
                                                        <option value="role" {{ old('level2_approval_type', $settings->level2_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level2_approval_type', $settings->level2_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level2_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level2_approvers" name="level2_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                <option value="role_{{ $role->name }}" {{ in_array('role_' . $role->name, old('level2_approvers', $settings->level2_approvers ?? ['role_admin'])) ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                <option value="user_{{ $user->id }}" {{ in_array('user_' . $user->id, old('level2_approvers', $settings->level2_approvers ?? [])) ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Level 3 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 3 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level3_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level3_approval_type" name="level3_approval_type">
                                                        <option value="role" {{ old('level3_approval_type', $settings->level3_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level3_approval_type', $settings->level3_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level3_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level3_approvers" name="level3_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                <option value="role_{{ $role->name }}" {{ in_array('role_' . $role->name, old('level3_approvers', $settings->level3_approvers ?? ['role_admin'])) ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                <option value="user_{{ $user->id }}" {{ in_array('user_' . $user->id, old('level3_approvers', $settings->level3_approvers ?? [])) ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Level 4 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 4 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level4_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level4_approval_type" name="level4_approval_type">
                                                        <option value="role" {{ old('level4_approval_type', $settings->level4_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level4_approval_type', $settings->level4_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level4_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level4_approvers" name="level4_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                <option value="role_{{ $role->name }}" {{ in_array('role_' . $role->name, old('level4_approvers', $settings->level4_approvers ?? ['role_super-admin'])) ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                <option value="user_{{ $user->id }}" {{ in_array('user_' . $user->id, old('level4_approvers', $settings->level4_approvers ?? [])) ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Level 5 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 5 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level5_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level5_approval_type" name="level5_approval_type">
                                                        <option value="role" {{ old('level5_approval_type', $settings->level5_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level5_approval_type', $settings->level5_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level5_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level5_approvers" name="level5_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                <option value="role_{{ $role->name }}" {{ in_array('role_' . $role->name, old('level5_approvers', $settings->level5_approvers ?? ['role_super-admin'])) ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                <option value="user_{{ $user->id }}" {{ in_array('user_' . $user->id, old('level5_approvers', $settings->level5_approvers ?? [])) ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> Update Approval Settings
                                    </button>
                                    <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Settings
                                    </a>
                                </div>
                            </div>
                        </form>
                        @else
                        <div class="alert alert-warning" role="alert">
                            <i class="bx bx-lock me-2"></i>
                            You don't have permission to manage payment voucher approval settings.
                        </div>
                        @endcan
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
document.addEventListener('DOMContentLoaded', function() {
    const approvalLevelsSelect = document.getElementById('approval_levels');
    const approvalCards = document.querySelectorAll('[id^="level"]');
    
    function toggleApprovalLevels() {
        const selectedLevels = parseInt(approvalLevelsSelect.value);
        
        // Show/hide approval level cards based on selection
        for (let i = 1; i <= 5; i++) {
            const levelCard = document.querySelector(`[id*="level${i}"]`).closest('.card');
            if (levelCard) {
                if (i <= selectedLevels) {
                    levelCard.style.display = 'block';
                } else {
                    levelCard.style.display = 'none';
                }
            }
        }
    }
    
    // Initial call
    toggleApprovalLevels();
    
    // Listen for changes
    approvalLevelsSelect.addEventListener('change', toggleApprovalLevels);
    
    // Dynamic approver loading based on approval type
    const approvalTypeSelects = document.querySelectorAll('[id$="_approval_type"]');
    const approverSelects = document.querySelectorAll('[id$="_approvers"]');
    
    function updateApproverOptions(approvalTypeSelect, approverSelect) {
        const selectedType = approvalTypeSelect.value;
        const currentValue = approverSelect.value;
        
        // Clear current options
        approverSelect.innerHTML = '';
        
        if (selectedType === 'role') {
            // Add role options
            @if(isset($roles))
                @foreach($roles as $role)
                    const roleOption = document.createElement('option');
                    roleOption.value = 'role_{{ $role->name }}';
                    roleOption.textContent = '{{ ucfirst($role->name) }} (Role)';
                    approverSelect.appendChild(roleOption);
                @endforeach
            @endif
        } else if (selectedType === 'user') {
            // Add user options
            @if(isset($users))
                @foreach($users as $user)
                    const userOption = document.createElement('option');
                    userOption.value = 'user_{{ $user->id }}';
                    userOption.textContent = '{{ $user->name }} ({{ $user->email }})';
                    approverSelect.appendChild(userOption);
                @endforeach
            @endif
        }
    }
    
    // Initialize and add event listeners
    approvalTypeSelects.forEach((typeSelect, index) => {
        const approverSelect = approverSelects[index];
        updateApproverOptions(typeSelect, approverSelect);
        
        typeSelect.addEventListener('change', function() {
            updateApproverOptions(this, approverSelect);
        });
    });
});
</script>
@endpush 