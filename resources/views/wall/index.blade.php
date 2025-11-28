<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Social Network</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        .header-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .search-bar {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .search-input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 15px;
        }
        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }
        .search-results {
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        .user-result {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            gap: 12px;
        }
        .user-result:hover {
            background: #f8f9fa;
        }
        .guest-prompt {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .guest-prompt h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        .guest-prompt p {
            color: #856404;
            margin-bottom: 15px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .create-post-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .create-post-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .post-textarea {
            width: 100%;
            min-height: 100px;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            resize: vertical;
            margin-bottom: 15px;
        }
        .post-textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .posts-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .post-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .post-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        .post-user-info {
            flex: 1;
        }
        .post-user-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
            margin-bottom: 2px;
        }
        .post-user-name a {
            color: inherit;
            text-decoration: none;
        }
        .post-user-name a:hover {
            color: #667eea;
        }
        .post-time {
            font-size: 13px;
            color: #999;
        }
        .post-content {
            font-size: 15px;
            line-height: 1.6;
            color: #333;
            margin-bottom: 15px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .post-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }
        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        .like-button {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 14px;
            color: #666;
        }
        .like-button:hover {
            background: #f8f9fa;
        }
        .like-button.liked {
            color: #e74c3c;
        }
        .delete-button {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
        }
        .delete-button:hover {
            background: #f8d7da;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: white;
        }
        .empty-state {
            background: white;
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }
        .empty-state p {
            color: #999;
            font-size: 14px;
        }
        /* Comment Styles */
        .comments-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        .comments-toggle {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .comments-toggle:hover {
            background: #f0f0ff;
        }
        .comments-list {
            margin-top: 15px;
        }
        .comment {
            padding: 12px 0;
            border-bottom: 1px solid #f8f8f8;
        }
        .comment:last-child {
            border-bottom: none;
        }
        .comment-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        .comment-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        .comment-author {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        .comment-time {
            font-size: 12px;
            color: #999;
        }
        .comment-content {
            font-size: 14px;
            color: #555;
            margin-left: 42px;
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .comment-actions {
            margin-left: 42px;
            display: flex;
            gap: 15px;
        }
        .comment-action-btn {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 12px;
            padding: 4px 0;
            transition: all 0.2s ease;
        }
        .comment-action-btn:hover {
            color: #764ba2;
        }
        .comment-action-btn.active {
            font-weight: 600;
        }
        .comment-form {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .comment-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            resize: none;
            font-family: inherit;
        }
        .comment-input:focus {
            outline: none;
            border-color: #667eea;
        }
        .comment-submit {
            background: #667eea;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .comment-submit:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        .reply-form {
            margin-left: 42px;
            margin-top: 10px;
        }
        .reply {
            margin-left: 42px;
            padding: 10px 0;
        }
        .share-button {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .share-button:hover {
            background: #f0f0ff;
        }
        .share-button.shared {
            color: #28a745;
            font-weight: 600;
        }
        .reaction-emoji {
            cursor: pointer;
            font-size: 16px;
            padding: 2px 5px;
            border-radius: 5px;
            transition: all 0.2s ease;
        }
        .reaction-emoji:hover {
            background: #f0f0ff;
            transform: scale(1.2);
        }
        .reaction-emoji.active {
            background: #e6ecff;
        }
        .reaction-picker {
            display: none;
            position: absolute;
            background: white;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            padding: 5px 10px;
            gap: 5px;
            z-index: 10;
        }
        .reaction-picker.show {
            display: flex;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 Wall</h1>
            <div class="header-buttons">
                @auth
                    <a href="/home" class="btn btn-primary">🏠 Home</a>
                    <a href="/profile" class="btn btn-primary">👤 Profile</a>
                    <a href="/chat" class="btn btn-primary">💬 Chat</a>
                @else
                    <a href="/" class="btn btn-primary">🏠 Home</a>
                    <a href="/login" class="btn btn-primary">🔑 Login</a>
                    <a href="/register" class="btn btn-primary">✨ Sign Up</a>
                @endauth
            </div>
        </div>

        <!-- Search Users -->
        <div class="search-bar">
            <input 
                type="text" 
                class="search-input" 
                id="searchInput" 
                placeholder="🔍 Search users by name or email..."
            >
            <div class="search-results" id="searchResults"></div>
        </div>

        @guest
        <!-- Guest Prompt -->
        <div class="guest-prompt">
            <h3>👋 Welcome to Our Social Wall!</h3>
            <p>You're viewing as a guest. Create an account to post, like, and connect with others!</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="/register" class="btn btn-primary">Create Account</a>
                <a href="/login" class="btn btn-secondary">Login</a>
            </div>
        </div>
        @endguest

        @auth
        <!-- Create Post -->
        <div class="create-post-card">
            <div class="create-post-header">
                <div class="user-avatar">
                    @if(Auth::user()->profile_image)
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="{{ Auth::user()->name }}">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    @endif
                </div>
                <div style="flex: 1;">
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
            </div>

            <form id="createPostForm" enctype="multipart/form-data">
                @csrf
                <textarea 
                    class="post-textarea" 
                    name="content" 
                    id="postContent" 
                    placeholder="What's on your mind, {{ Auth::user()->name }}? (or just add a photo)"
                ></textarea>
                
                <img id="imagePreview" class="image-preview">
                
                <div class="post-actions">
                    <div>
                        <label for="postImage" class="btn btn-secondary" style="cursor: pointer;">
                            📷 Add Photo
                        </label>
                        <input type="file" id="postImage" name="image" accept="image/*" style="display: none;">
                    </div>
                    <button type="submit" class="btn btn-success">📮 Post</button>
                </div>
            </form>
        </div>
        @endauth

        <!-- Posts Feed -->
        <div id="postsContainer" class="posts-container">
            <div class="loading">Loading posts...</div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const currentUserId = @json(Auth::id());
            const isAuthenticated = @json(Auth::check());

            // User search
            let searchTimeout;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();
                
                if (query.length < 2) {
                    $('#searchResults').html('');
                    return;
                }
                
                searchTimeout = setTimeout(function() { searchUsers(query); }, 300);
            });

            function searchUsers(query) {
                $.ajax({
                    url: `/users/search?q=${encodeURIComponent(query)}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.results.length === 0) {
                            $('#searchResults').html('<div style="padding: 15px; text-align: center; color: #999;">No users found</div>');
                            return;
                        }
                        
                        const resultsHtml = data.results.map(user => {
                            const initials = user.name.split(' ').map(n => n[0]).join('').substring(0, 2);
                            const avatarContent = user.profile_image 
                                ? `<img src="${user.profile_image}" alt="${user.name}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">`
                                : `<div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">${initials}</div>`;
                            
                            return `
                                <div class="user-result" data-user-id="${user.id}">
                                    ${avatarContent}
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; color: #333;">${user.name}</div>
                                        <div style="font-size: 13px; color: #666;">${user.email}</div>
                                        <div style="font-size: 12px; color: #28a745; margin-top: 2px;">✓ Registered</div>
                                    </div>
                                </div>
                            `;
                        }).join('');
                        
                        $('#searchResults').html(resultsHtml);
                    },
                    error: function(error) {
                        console.error('Search error:', error);
                    }
                });
            }

            // Event delegation for search results
            $(document).on('click', '.user-result', function() {
                const userId = $(this).data('user-id');
                window.location.href = `/profile/${userId}`;
            });

            // Image preview (only if authenticated)
            if (isAuthenticated) {
                $('#postImage').on('change', function(e) {
                    const file = e.target.files[0];
                    
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#imagePreview').attr('src', e.target.result).show();
                        }
                        reader.readAsDataURL(file);
                    } else {
                        $('#imagePreview').hide();
                    }
                });

                // Create post
                $('#createPostForm').on('submit', function(e) {
                    e.preventDefault();

                    const content = $('#postContent').val().trim();
                    const hasImage = $('#postImage')[0].files.length > 0;

                    if (!content && !hasImage) {
                        alert('Please enter some content or add a photo');
                        return;
                    }
                    
                    const formData = new FormData(this);
                    const $submitBtn = $(this).find('button[type="submit"]');
                    $submitBtn.prop('disabled', true).text('Posting...');
                    
                    $.ajax({
                        url: '/posts',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(result) {
                            if (result.success) {
                                $('#postContent').val('');
                                $('#postImage').val('');
                                $('#imagePreview').hide();
                                loadPosts();
                                alert('✅ Post created successfully!');
                            } else {
                                alert('❌ ' + (result.message || 'Failed to create post'));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', xhr.responseJSON || error);
                            const errorMsg = xhr.responseJSON?.message || 'Error creating post';
                            alert('❌ ' + errorMsg);
                        },
                        complete: function() {
                            $submitBtn.prop('disabled', false).text('📮 Post');
                        }
                    });
                });
            }

            // Load posts
            function loadPosts() {
                $.ajax({
                    url: '/posts',
                    type: 'GET',
                    dataType: 'json',
                    success: function(posts) {
                        if (posts.length === 0) {
                            $('#postsContainer').html(`
                                <div class="empty-state">
                                    <h3>No posts yet</h3>
                                    <p>Be the first to share something with your friends!</p>
                                </div>
                            `);
                            return;
                        }
                        
                        const postsHtml = posts.map(post => {
                            const avatarContent = post.user.profile_image 
                                ? `<img src="${post.user.profile_image}" alt="${post.user.name}">`
                                : post.user.name.split(' ').map(n => n[0]).join('').substring(0, 2);
                            
                            const imageHtml = post.image ? `<img src="${post.image}" class="post-image">` : '';
                            
                            const likeButtonClass = post.is_liked ? 'liked' : '';
                            const likeIcon = post.is_liked ? '❤️' : '🤍';
                            
                            const deleteButton = post.user.id === currentUserId 
                                ? `<button class="delete-button" data-post-id="${post.id}">🗑️ Delete</button>`
                                : '';
                            
                            return `
                                <div class="post-card">
                                    <div class="post-header">
                                        <div class="user-avatar">${avatarContent}</div>
                                        <div class="post-user-info">
                                            <div class="post-user-name">
                                                <a href="/profile/${post.user.id}">${post.user.name}</a>
                                            </div>
                                            <div class="post-time">${post.created_at}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="post-content">${escapeHtml(post.content)}</div>
                                    
                                    ${imageHtml}
                                    
                                    <div class="post-footer">
                                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                            <button class="like-button ${likeButtonClass}" data-post-id="${post.id}">
                                                <span class="like-icon">${likeIcon}</span>
                                                <span class="like-count">${post.likes_count}</span> Likes
                                            </button>
                                            <button class="comments-toggle" data-post-id="${post.id}">
                                                💬 <span class="comments-count">${post.comments_count || 0}</span> Comments
                                            </button>
                                            <button class="share-button ${post.is_shared ? 'shared' : ''}" data-post-id="${post.id}">
                                                🔗 <span class="shares-count">${post.shares_count || 0}</span> Shares
                                            </button>
                                        </div>
                                        ${deleteButton}
                                    </div>
                                    
                                    <div class="comments-section" id="comments-${post.id}" style="display: none;">
                                        <div class="comments-list" id="comments-list-${post.id}"></div>
                                        @auth
                                        <div class="comment-form">
                                            <textarea class="comment-input" placeholder="Write a comment..." rows="1" data-post-id="${post.id}"></textarea>
                                            <button class="comment-submit" data-post-id="${post.id}">Post</button>
                                        </div>
                                        @endauth
                                    </div>
                                </div>
                            `;
                        }).join('');
                        
                        $('#postsContainer').html(postsHtml);
                    },
                    error: function(error) {
                        console.error('Error loading posts:', error);
                        $('#postsContainer').html('<div class="empty-state"><h3>Error loading posts</h3></div>');
                    }
                });
            }

            // Toggle like with event delegation
            $(document).on('click', '.like-button', function() {
                const postId = $(this).data('post-id');
                const $button = $(this);
                
                if (!isAuthenticated) {
                    if (confirm('Please login to like posts. Go to login page?')) {
                        window.location.href = '/login';
                    }
                    return;
                }
                
                $.ajax({
                    url: `/posts/${postId}/like`,
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(result) {
                        if (result.success) {
                            $button.find('.like-icon').text(result.liked ? '❤️' : '🤍');
                            $button.find('.like-count').text(result.likes_count);
                            
                            if (result.liked) {
                                $button.addClass('liked');
                            } else {
                                $button.removeClass('liked');
                            }
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            // Delete post with event delegation
            $(document).on('click', '.delete-button', function() {
                const postId = $(this).data('post-id');
                
                if (!confirm('Are you sure you want to delete this post?')) {
                    return;
                }
                
                $.ajax({
                    url: `/posts/${postId}`,
                    type: 'DELETE',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(result) {
                        if (result.success) {
                            loadPosts();
                            alert('Post deleted successfully');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                        alert('Error deleting post');
                    }
                });
            });

            // Toggle comments section
            $(document).on('click', '.comments-toggle', function() {
                const postId = $(this).data('post-id');
                const $commentsSection = $(`#comments-${postId}`);
                
                if ($commentsSection.is(':visible')) {
                    $commentsSection.slideUp();
                } else {
                    $commentsSection.slideDown();
                    loadComments(postId);
                }
            });

            // Load comments for a post
            function loadComments(postId) {
                $.ajax({
                    url: `/posts/${postId}/comments`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(comments) {
                        renderComments(postId, comments);
                    },
                    error: function(error) {
                        console.error('Error loading comments:', error);
                    }
                });
            }

            // Render comments
            function renderComments(postId, comments) {
                const $commentsList = $(`#comments-list-${postId}`);
                
                if (comments.length === 0) {
                    $commentsList.html('<p style="color: #999; font-size: 14px; text-align: center; padding: 20px;">No comments yet. Be the first to comment!</p>');
                    return;
                }
                
                const commentsHtml = comments.map(comment => renderComment(comment)).join('');
                $commentsList.html(commentsHtml);
            }

            // Render single comment
            function renderComment(comment) {
                const avatar = comment.user.profile_image || 'https://via.placeholder.com/32';
                const replyButton = isAuthenticated ? `<button class="comment-action-btn reply-btn" data-comment-id="${comment.id}">Reply</button>` : '';
                const deleteButton = (isAuthenticated && comment.user_id == currentUserId) ? `<button class="comment-action-btn delete-comment-btn" data-comment-id="${comment.id}">Delete</button>` : '';
                
                // Render reactions
                const reactionTypes = ['like', 'love', 'laugh', 'wow', 'sad', 'angry'];
                const reactionEmojis = {like: '👍', love: '❤️', laugh: '😂', wow: '😮', sad: '😢', angry: '😠'};
                let reactionsHtml = '';
                
                if (isAuthenticated) {
                    reactionsHtml = '<div style="position: relative; display: inline-block;">';
                    reactionsHtml += `<button class="comment-action-btn react-btn" data-comment-id="${comment.id}">React</button>`;
                    reactionsHtml += `<div class="reaction-picker" id="reaction-picker-${comment.id}">`;
                    reactionTypes.forEach(type => {
                        const activeClass = comment.user_reaction === type ? 'active' : '';
                        reactionsHtml += `<span class="reaction-emoji ${activeClass}" data-comment-id="${comment.id}" data-reaction="${type}">${reactionEmojis[type]}</span>`;
                    });
                    reactionsHtml += '</div></div>';
                }
                
                // Show reaction counts
                let reactionCountsHtml = '';
                if (comment.reaction_counts && Object.keys(comment.reaction_counts).length > 0) {
                    reactionCountsHtml = '<span style="margin-left: 10px; font-size: 12px; color: #999;">';
                    Object.entries(comment.reaction_counts).forEach(([type, count]) => {
                        reactionCountsHtml += `${reactionEmojis[type]} ${count} `;
                    });
                    reactionCountsHtml += '</span>';
                }
                
                let repliesHtml = '';
                if (comment.replies && comment.replies.length > 0) {
                    repliesHtml = comment.replies.map(reply => `<div class="reply">${renderComment(reply)}</div>`).join('');
                }
                
                return `
                    <div class="comment">
                        <div class="comment-header">
                            <img src="${avatar}" alt="${comment.user.name}" class="comment-avatar">
                            <span class="comment-author">${escapeHtml(comment.user.name)}</span>
                            <span class="comment-time">${comment.created_at}</span>
                        </div>
                        <div class="comment-content">${escapeHtml(comment.content)}</div>
                        <div class="comment-actions">
                            ${reactionsHtml}
                            ${reactionCountsHtml}
                            ${replyButton}
                            ${deleteButton}
                        </div>
                        <div class="reply-form-container" id="reply-form-${comment.id}"></div>
                        ${repliesHtml}
                    </div>
                `;
            }

            // Post comment
            $(document).on('click', '.comment-submit', function() {
                const postId = $(this).data('post-id');
                const $input = $(`.comment-input[data-post-id="${postId}"]`);
                const content = $input.val().trim();
                
                if (!content) {
                    alert('Please write a comment');
                    return;
                }
                
                $.ajax({
                    url: '/comments',
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        post_id: postId,
                        content: content
                    },
                    success: function(result) {
                        if (result.success) {
                            $input.val('');
                            loadComments(postId);
                            // Update comment count
                            $(`.comments-toggle[data-post-id="${postId}"] .comments-count`).text(parseInt($(`.comments-toggle[data-post-id="${postId}"] .comments-count`).text()) + 1);
                        }
                    },
                    error: function(error) {
                        console.error('Error posting comment:', error);
                        alert('Error posting comment');
                    }
                });
            });

            // Reply to comment
            $(document).on('click', '.reply-btn', function() {
                const commentId = $(this).data('comment-id');
                const $replyFormContainer = $(`#reply-form-${commentId}`);
                
                if ($replyFormContainer.children().length > 0) {
                    $replyFormContainer.empty();
                    return;
                }
                
                const replyFormHtml = `
                    <div class="reply-form">
                        <textarea class="comment-input reply-input" placeholder="Write a reply..." rows="1" data-comment-id="${commentId}"></textarea>
                        <button class="comment-submit reply-submit" data-comment-id="${commentId}">Reply</button>
                    </div>
                `;
                $replyFormContainer.html(replyFormHtml);
            });

            // Post reply
            $(document).on('click', '.reply-submit', function() {
                const commentId = $(this).data('comment-id');
                const $input = $(`.reply-input[data-comment-id="${commentId}"]`);
                const content = $input.val().trim();
                
                // Find the post ID from the comment section
                const $commentsSection = $input.closest('.comments-section');
                const postId = $commentsSection.attr('id').replace('comments-', '');
                
                if (!content) {
                    alert('Please write a reply');
                    return;
                }
                
                $.ajax({
                    url: '/comments',
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        post_id: postId,
                        parent_id: commentId,
                        content: content
                    },
                    success: function(result) {
                        if (result.success) {
                            loadComments(postId);
                        }
                    },
                    error: function(error) {
                        console.error('Error posting reply:', error);
                        alert('Error posting reply');
                    }
                });
            });

            // Delete comment
            $(document).on('click', '.delete-comment-btn', function() {
                const commentId = $(this).data('comment-id');
                
                if (!confirm('Are you sure you want to delete this comment?')) {
                    return;
                }
                
                $.ajax({
                    url: `/comments/${commentId}`,
                    type: 'DELETE',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(result) {
                        if (result.success) {
                            // Find the post ID
                            const $commentsSection = $(`#reply-form-${commentId}`).closest('.comments-section');
                            const postId = $commentsSection.attr('id').replace('comments-', '');
                            loadComments(postId);
                            // Update comment count
                            $(`.comments-toggle[data-post-id="${postId}"] .comments-count`).text(parseInt($(`.comments-toggle[data-post-id="${postId}"] .comments-count`).text()) - 1);
                        }
                    },
                    error: function(error) {
                        console.error('Error deleting comment:', error);
                        alert('Error deleting comment');
                    }
                });
            });

            // Show reaction picker
            $(document).on('click', '.react-btn', function(e) {
                e.stopPropagation();
                const commentId = $(this).data('comment-id');
                $('.reaction-picker').removeClass('show');
                $(`#reaction-picker-${commentId}`).addClass('show');
            });

            // Hide reaction picker when clicking outside
            $(document).on('click', function() {
                $('.reaction-picker').removeClass('show');
            });

            // React to comment
            $(document).on('click', '.reaction-emoji', function(e) {
                e.stopPropagation();
                const commentId = $(this).data('comment-id');
                const reactionType = $(this).data('reaction');
                
                $.ajax({
                    url: `/comments/${commentId}/react`,
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        reaction_type: reactionType
                    },
                    success: function(result) {
                        if (result.success) {
                            // Find the post ID
                            const $commentsSection = $(`.reaction-emoji[data-comment-id="${commentId}"]`).closest('.comments-section');
                            const postId = $commentsSection.attr('id').replace('comments-', '');
                            loadComments(postId);
                        }
                    },
                    error: function(error) {
                        console.error('Error reacting to comment:', error);
                    }
                });
            });

            // Share post
            $(document).on('click', '.share-button', function() {
                const postId = $(this).data('post-id');
                const $button = $(this);
                
                if (!isAuthenticated) {
                    if (confirm('Please login to share posts. Go to login page?')) {
                        window.location.href = '/login';
                    }
                    return;
                }
                
                $.ajax({
                    url: `/posts/${postId}/share`,
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(result) {
                        if (result.success) {
                            $button.find('.shares-count').text(result.shares_count);
                            if (result.shared) {
                                $button.addClass('shared');
                            } else {
                                $button.removeClass('shared');
                            }
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            // Escape HTML
            function escapeHtml(text) {
                return $('<div>').text(text).html();
            }

            // Initial load
            loadPosts();

            // Refresh every 30 seconds
            setInterval(loadPosts, 30000);
        });
    </script>
</body>
</html>

