@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item" aria-current="page">Dashboard</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Dashboard</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ breadcrumb ] end -->

<!-- [ Main Content ] start -->
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-6 col-xl-3">
        <div class="card bg-primary-500 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 mb-1">Total Users</p>
                        <h4 class="mb-0 text-white">{{ $stats['total_users'] }}</h4>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <i data-feather="users" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card bg-success-500 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 mb-1">Verified Users</p>
                        <h4 class="mb-0 text-white">{{ $stats['verified_users'] }}</h4>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <i data-feather="check-circle" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card bg-warning-500 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 mb-1">Total Posts</p>
                        <h4 class="mb-0 text-white">{{ $stats['total_posts'] }}</h4>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <i data-feather="file-text" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card bg-danger-500 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 mb-1">Total Comments</p>
                        <h4 class="mb-0 text-white">{{ $stats['total_comments'] }}</h4>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <i data-feather="message-square" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Users -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Users</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stats['recent_users'] as $user)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            @if ($user->profile_image)
                                                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover" />
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <span>{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge bg-{{ $user->role->value === 'admin' ? 'danger' : 'primary' }}">{{ $user->role->label() }}</span></td>
                                    <td>
                                        @if ($user->email_verified_at)
                                            <span class="badge bg-success">Verified</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No users yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Posts -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Posts</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Content</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stats['recent_posts'] as $post)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            @if ($post->user->profile_image)
                                                <img src="{{ asset('storage/' . $post->user->profile_image) }}" alt="{{ $post->user->name }}" class="w-8 h-8 rounded-full object-cover" />
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white">
                                                    {{ substr($post->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <span>{{ $post->user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ Str::limit($post->content ?? 'Image post', 50) }}</td>
                                    <td>{{ $post->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No posts yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->
@endsection

