@extends('admin.layouts.app')

@section('title', 'Manage Posts')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item" aria-current="page">Posts</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Manage Posts</h2>
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
                <h5 class="mb-0">Posts List</h5>
                <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                    <i data-feather="plus" class="w-4 h-4 me-1"></i>
                    Add Post
                </a>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('admin.posts.index') }}" class="mb-4" id="filter-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by content..." value="{{ request('search') }}" autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-full">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary w-full">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Posts Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Content</th>
                                <th>Image</th>
                                <th>Likes</th>
                                <th>Comments</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($posts as $post)
                                <tr data-post-id="{{ $post->id }}">
                                    <td>{{ $post->id }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            @if ($post->user->profile_image)
                                                <img src="{{ asset('storage/' . $post->user->profile_image) }}" alt="{{ $post->user->name }}" class="w-8 h-8 rounded-full object-cover" />
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white text-sm">
                                                    {{ substr($post->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <span>{{ $post->user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="editable" data-field="content" data-record-id="{{ $post->id }}" data-record-type="posts" data-original-value="{{ $post->content }}">{{ Str::limit($post->content ?? 'Image post', 50) }}</td>
                                    <td>
                                        @if ($post->image)
                                            <img src="{{ asset('storage/' . $post->image) }}" alt="Post image" class="w-16 h-16 object-cover rounded" />
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>{{ $post->likes->count() }}</td>
                                    <td>{{ $post->comments->count() }}</td>
                                    <td>{{ $post->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-sm btn-info" title="View">
                                                <i data-feather="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i data-feather="edit" class="w-4 h-4"></i>
                                            </a>
                                            <button type="button" onclick="deletePost({{ $post->id }}, '{{ addslashes($post->content) }}')" class="btn btn-sm btn-danger" title="Delete">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No posts found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $posts->links() }}
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
    // Live search with debounce for posts
    $('#search-input').on('keyup', function() {
        const query = $(this).val();
        clearTimeout(window.searchTimeout);
        
        window.searchTimeout = setTimeout(function() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('search', query);
            
            showLoading('.card-body');
            
            $.ajax({
                url: currentUrl.toString(),
                type: 'GET',
                success: function(html) {
                    hideLoading();
                    const newTableBody = $(html).find('tbody').html();
                    $('tbody').html(newTableBody);
                    
                    // Reinitialize feather icons
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    showToast('Failed to search posts', 'error');
                }
            });
        }, 500);
    });
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
@endpush

