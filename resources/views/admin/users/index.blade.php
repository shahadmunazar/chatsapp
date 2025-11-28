@extends('admin.layouts.app')

@section('title', 'Manage Users')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item" aria-current="page">Users</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Manage Users</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ breadcrumb ] end -->

<!-- [ Main Content ] start -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h5 class="mb-0">Users List</h5>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i data-feather="plus" class="w-4 h-4 me-1"></i>
                    Add User
                </a>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4" id="filter-form">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <select name="role" id="role-filter" class="form-select">
                                <option value="">All Roles</option>
                                @foreach (\App\UserRole::cases() as $role)
                                    <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>
                                        {{ $role->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-full">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary w-full">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr data-user-id="{{ $user->id }}">
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            @if ($user->profile_image)
                                                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover" />
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <span class="editable" data-field="name" data-record-id="{{ $user->id }}" data-record-type="users" data-original-value="{{ $user->name }}">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="editable" data-field="email" data-record-id="{{ $user->id }}" data-record-type="users" data-original-value="{{ $user->email }}">{{ $user->email }}</td>
                                    <td><span class="badge bg-{{ $user->role->value === 'admin' ? 'danger' : ($user->role->value === 'moderator' ? 'warning' : 'primary') }}">{{ $user->role->label() }}</span></td>
                                    <td>
                                        @if ($user->email_verified_at)
                                            <span class="badge bg-success status-badge">Verified</span>
                                        @else
                                            <span class="badge bg-warning status-badge">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info" title="View">
                                                <i data-feather="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i data-feather="edit" class="w-4 h-4"></i>
                                            </a>
                                            @if ($user->id !== auth()->id())
                                                <button type="button" onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')" class="btn btn-sm btn-danger" title="Delete">
                                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Live search with debounce
    $('#search-input').on('keyup', function() {
        const query = $(this).val();
        searchUsers(query);
    });
    
    // Auto-submit on role change
    $('#role-filter').on('change', function() {
        $('#filter-form').submit();
    });
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
@endpush

