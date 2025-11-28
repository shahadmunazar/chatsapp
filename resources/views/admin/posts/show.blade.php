@extends('admin.layouts.app')

@section('title', 'View Post')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">Posts</a></li>
                    <li class="breadcrumb-item" aria-current="page">View</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Post Details</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ breadcrumb ] end -->

<!-- [ Main Content ] start -->
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if ($post->user->profile_image)
                        <img src="{{ asset('storage/' . $post->user->profile_image) }}" alt="{{ $post->user->name }}" class="w-12 h-12 rounded-full object-cover" />
                    @else
                        <div class="w-12 h-12 rounded-full bg-primary-500 flex items-center justify-center text-white">
                            {{ substr($post->user->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h5 class="mb-0">{{ $post->user->name }}</h5>
                        <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                <span class="badge bg-primary">{{ $post->likes->count() }} Likes</span>
            </div>
            <div class="card-body">
                @if ($post->content)
                    <p class="mb-3">{{ $post->content }}</p>
                @endif

                @if ($post->image)
                    <img src="{{ asset('storage/' . $post->image) }}" alt="Post image" class="w-full rounded mb-3" />
                @endif

                <div class="border-top pt-3 mt-3">
                    <h6>Statistics</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="text-primary">{{ $post->likes->count() }}</h3>
                                <p class="mb-0">Likes</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="text-success">{{ $post->comments->count() }}</h3>
                                <p class="mb-0">Comments</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="text-warning">{{ $post->shares->count() }}</h3>
                                <p class="mb-0">Shares</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Comments ({{ $post->comments->count() }})</h5>
            </div>
            <div class="card-body">
                @forelse ($post->comments->take(10) as $comment)
                    <div class="flex gap-3 mb-3 pb-3 border-bottom">
                        @if ($comment->user->profile_image)
                            <img src="{{ asset('storage/' . $comment->user->profile_image) }}" alt="{{ $comment->user->name }}" class="w-10 h-10 rounded-full object-cover" />
                        @else
                            <div class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white">
                                {{ substr($comment->user->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <strong>{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0">{{ $comment->content }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted">No comments yet</p>
                @endforelse
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-warning">Edit Post</a>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Back to List</a>
            <button type="button" onclick="deletePost({{ $post->id }}, '{{ addslashes($post->content) }}')" class="btn btn-danger">Delete Post</button>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->
@endsection

@push('scripts')
<script>
// Override deletePost to redirect to index after deletion
const originalDeletePost = window.deletePost;
window.deletePost = function(postId, postContent) {
    confirmAction(`Are you sure you want to delete this post?`, function() {
        showLoading();
        
        $.ajax({
            url: `/admin/posts/${postId}`,
            type: 'DELETE',
            success: function(response) {
                hideLoading();
                showToast('Post deleted successfully!', 'success');
                
                // Redirect to posts list
                setTimeout(function() {
                    window.location.href = '{{ route('admin.posts.index') }}';
                }, 1500);
            },
            error: function(xhr) {
                hideLoading();
                const message = xhr.responseJSON?.message || 'Failed to delete post';
                showToast(message, 'error');
            }
        });
    });
};
</script>
@endpush

