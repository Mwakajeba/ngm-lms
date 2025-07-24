@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST">
    @csrf
    @if(isset($user))
        @method('PUT')
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Select Branch</label>
            <select class="form-select" name="branch_id" required>
                <option value="">-- Choose Branch --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ (old('branch_id') == $branch->id || (isset($user) && $user->branch_id == $branch->id)) ? 'selected' : '' }}>
                        {{ $branch->name }} ({{ $branch->company->name ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">User Role</label>
            <select class="form-select" name="role" required>
                <option value="">-- Choose Role --</option>
                <option value="admin" {{ (old('role') == 'admin' || (isset($user) && $user->role == 'admin')) ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ (old('role') == 'manager' || (isset($user) && $user->role == 'manager')) ? 'selected' : '' }}>Manager</option>
                <option value="teller" {{ (old('role') == 'teller' || (isset($user) && $user->role == 'teller')) ? 'selected' : '' }}>Teller</option>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" value="{{ $user->name ?? old('name') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone" value="{{ $user->phone ?? old('phone') }}" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="{{ $user->email ?? old('email') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ isset($user) ? 'New Password (leave blank to keep current)' : 'Password' }}</label>
            <input type="password" class="form-control" name="password" {{ isset($user) ? '' : 'required' }}>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" name="is_active" required>
                <option value="">-- Select Status --</option>
                <option value="yes" {{ (old('is_active') ?? $user->is_active ?? '') == 'yes' ? 'selected' : '' }}>Active</option>
                <option value="no" {{ (old('is_active') ?? $user->is_active ?? '') == 'no' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="col-md-6">
            
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-{{ isset($user) ? 'primary' : 'success' }}">
            {{ isset($user) ? 'Update User' : 'Create User' }}
        </button>
    </div>
</form>