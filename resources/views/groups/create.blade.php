@extends('layouts.main')

@section('title', 'Create Group')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Groups', 'url' => route('groups.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Create Group', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />
            <h6 class="mb-0 text-uppercase">CREATE GROUP</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">Add New Group</h4>

                            </div>

                            @if($errors->any())
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

                            <form action="{{ route('groups.store') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <!-- Group Name -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Group Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" placeholder="Enter group name" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Loan Officer -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Loan Officer <span class="text-danger">*</span></label>
                                        <select name="loan_officer"
                                            class="form-select @error('loan_officer') is-invalid @enderror" required>
                                            <option value="">-- Select Loan Officer --</option>
                                            @foreach($loanOfficers as $officer)
                                                <option value="{{ $officer->id }}" {{ old('loan_officer') == $officer->id ? 'selected' : '' }}>
                                                    {{ $officer->name }} ({{ $officer->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('loan_officer')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Minimum Members -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Minimum Members <span class="text-danger">*</span></label>
                                        <input type="number" name="minimum_members"
                                            class="form-control @error('minimum_members') is-invalid @enderror"
                                            value="{{ old('minimum_members', 5) }}" min="1" max="50" required>
                                        @error('minimum_members')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Maximum Members -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Maximum Members <span class="text-danger">*</span></label>
                                        <input type="number" name="maximum_members"
                                            class="form-control @error('maximum_members') is-invalid @enderror"
                                            value="{{ old('maximum_members', 20) }}" min="1" max="100" required>
                                        @error('maximum_members')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Group Leader -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Group Leader</label>
                                        <select name="group_leader"
                                            class="form-select @error('group_leader') is-invalid @enderror">
                                            <option value="">-- Select Group Leader --</option>
                                            @foreach($groupLeaders as $leader)
                                                <option value="{{ $leader->id }}" {{ old('group_leader') == $leader->id ? 'selected' : '' }}>
                                                    {{ $leader->name }} ({{ $leader->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('group_leader')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Meeting Day -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Meeting Day <span class="text-danger">*</span></label>
                                        <select name="meeting_day"
                                            class="form-select @error('meeting_day') is-invalid @enderror" required>
                                            <option value="">-- Select Meeting Day --</option>
                                            <option value="monday" {{ old('meeting_day') == 'monday' ? 'selected' : '' }}>
                                                Monday</option>
                                            <option value="tuesday" {{ old('meeting_day') == 'tuesday' ? 'selected' : '' }}>
                                                Tuesday</option>
                                            <option value="wednesday" {{ old('meeting_day') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                            <option value="thursday" {{ old('meeting_day') == 'thursday' ? 'selected' : '' }}>
                                                Thursday</option>
                                            <option value="friday" {{ old('meeting_day') == 'friday' ? 'selected' : '' }}>
                                                Friday</option>
                                            <option value="saturday" {{ old('meeting_day') == 'saturday' ? 'selected' : '' }}>
                                                Saturday</option>
                                            <option value="sunday" {{ old('meeting_day') == 'sunday' ? 'selected' : '' }}>
                                                Sunday</option>
                                        </select>
                                        @error('meeting_day')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Meeting Time -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Meeting Time (Optional)</label>
                                        <input type="time" name="meeting_time"
                                            class="form-control @error('meeting_time') is-invalid @enderror"
                                            value="{{ old('meeting_time') }}">
                                        @error('meeting_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('groups.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-x"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save"></i> Create Group
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .btn {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .alert {
            border-radius: 0.5rem;
            border: none;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .invalid-feedback {
            font-size: 0.875rem;
            color: #dc3545;
        }
    </style>
@endpush