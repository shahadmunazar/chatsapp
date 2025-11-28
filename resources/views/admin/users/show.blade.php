@extends('admin.layouts.app')

@section('title', 'View User')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item" aria-current="page">View</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">User Details</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ breadcrumb ] end -->

<!-- [ Main Content ] start -->
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if ($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-32 h-32 mx-auto rounded-full object-cover mb-3" />
                @else
                    <div class="w-32 h-32 mx-auto rounded-full bg-primary-500 flex items-center justify-center text-white text-4xl mb-3">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                @endif
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                <span class="badge bg-{{ $user->role->value === 'admin' ? 'danger' : ($user->role->value === 'moderator' ? 'warning' : 'primary') }}">
                    {{ $user->role->label() }}
                </span>
                
                @if ($user->email_verified_at)
                    <span class="badge bg-success ms-2">Verified</span>
                @else
                    <span class="badge bg-warning ms-2">Pending</span>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Statistics</h5>
            </div>
            <div class="card-body">
                <div class="flex justify-between mb-2">
                    <span>Posts:</span>
                    <strong>{{ $user->posts->count() }}</strong>
                </div>
                <div class="flex justify-between mb-2">
                    <span>Friends:</span>
                    <strong>{{ $user->friends()->count() }}</strong>
                </div>
                <div class="flex justify-between mb-2">
                    <span>Joined:</span>
                    <strong>{{ $user->created_at->format('M d, Y') }}</strong>
                </div>
                <div class="flex justify-between">
                    <span>Last Seen:</span>
                    <strong>{{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Profile Information</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td width="200"><strong>Bio:</strong></td>
                            <td>{{ $user->bio ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Date of Birth:</strong></td>
                            <td>{{ $user->date_of_birth ? $user->date_of_birth->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Gender:</strong></td>
                            <td>{{ $user->gender ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>School:</strong></td>
                            <td>{{ $user->school ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>College:</strong></td>
                            <td>{{ $user->college ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Work:</strong></td>
                            <td>{{ $user->work ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Location:</strong></td>
                            <td>
                                @php
                                    $location = array_filter([$user->city, $user->state, $user->country]);
                                @endphp
                                {{ !empty($location) ? implode(', ', $location) : 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Website:</strong></td>
                            <td>
                                @if ($user->website)
                                    <a href="{{ $user->website }}" target="_blank">{{ $user->website }}</a>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Recent Posts</h5>
            </div>
            <div class="card-body">
                @forelse ($user->posts->take(5) as $post)
                    <div class="mb-3 pb-3 border-bottom">
                        <p class="mb-2">{{ Str::limit($post->content ?? 'Image post', 100) }}</p>
                        <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                    </div>
                @empty
                    <p class="text-center text-muted">No posts yet</p>
                @endforelse
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">Edit User</a>
            <a href="{{ route('profile.view', $user->id) }}" target="_blank" class="btn btn-info">View Public Profile</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->
@endsection

