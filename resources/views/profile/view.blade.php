<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }}'s Profile</title>
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
            max-width: 700px;
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
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            background: #667eea;
            color: white;
        }
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: bold;
            margin: 0 auto 20px;
            overflow: hidden;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-name {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .user-email {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }
        .user-status {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            background: #f8f9fa;
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-online {
            background: #28a745;
        }
        .status-offline {
            background: #6c757d;
        }
        .posts-section {
            margin-top: 30px;
        }
        .posts-header {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .post-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .post-content {
            font-size: 15px;
            line-height: 1.6;
            color: #333;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin-bottom: 10px;
        }
        .post-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
        }
        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #666;
        }
        .profile-details {
            margin-top: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
        }
        .profile-details h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
            width: 150px;
            flex-shrink: 0;
        }
        .detail-value {
            color: #333;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👤 User Profile</h1>
            <a href="/home" class="btn">← Back to Home</a>
        </div>

        <div class="profile-card">
            <div class="avatar">
                @if($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}">
                @else
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                @endif
            </div>

            <div class="user-name">{{ $user->name }}</div>
            <div class="user-email">{{ $user->email }}</div>

            <div class="user-status">
                <span class="status-indicator {{ $user->isOnline() ? 'status-online' : 'status-offline' }}"></span>
                <span>{{ $user->last_seen ?? 'Offline' }}</span>
            </div>

            @if($user->bio || $user->school || $user->college || $user->work || $user->city || $user->website)
            <div class="profile-details">
                <h3>📋 About</h3>
                
                @if($user->bio)
                <div class="detail-row">
                    <div class="detail-label">Bio:</div>
                    <div class="detail-value">{{ $user->bio }}</div>
                </div>
                @endif

                @if($user->school)
                <div class="detail-row">
                    <div class="detail-label">🎓 School:</div>
                    <div class="detail-value">{{ $user->school }}</div>
                </div>
                @endif

                @if($user->college)
                <div class="detail-row">
                    <div class="detail-label">🎓 College:</div>
                    <div class="detail-value">{{ $user->college }}</div>
                </div>
                @endif

                @if($user->work)
                <div class="detail-row">
                    <div class="detail-label">💼 Work:</div>
                    <div class="detail-value">{{ $user->work }}</div>
                </div>
                @endif

                @if($user->city || $user->country)
                <div class="detail-row">
                    <div class="detail-label">📍 Location:</div>
                    <div class="detail-value">
                        {{ $user->city }}{{ $user->city && $user->country ? ', ' : '' }}{{ $user->country }}
                    </div>
                </div>
                @endif

                @if($user->website)
                <div class="detail-row">
                    <div class="detail-label">🌐 Website:</div>
                    <div class="detail-value">
                        <a href="{{ $user->website }}" target="_blank" style="color: #667eea;">{{ $user->website }}</a>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="posts-section">
                <div class="posts-header">📝 Posts</div>
                <div id="postsContainer">
                    <div style="text-align: center; color: #999;">Loading posts...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Load user posts
            function loadPosts() {
                $.ajax({
                    url: '/posts/user/{{ $user->id }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(posts) {
                        if (posts.length === 0) {
                            $('#postsContainer').html('<div style="text-align: center; color: #999;">No posts yet</div>');
                            return;
                        }
                        
                        const postsHtml = posts.map(post => {
                            const imageHtml = post.image ? `<img src="${post.image}" class="post-image">` : '';
                            
                            return `
                                <div class="post-card">
                                    <div class="post-content">${escapeHtml(post.content)}</div>
                                    ${imageHtml}
                                    <div class="post-footer">
                                        <span>❤️ ${post.likes_count} Likes</span>
                                        <span>${post.created_at}</span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                        
                        $('#postsContainer').html(postsHtml);
                    },
                    error: function(error) {
                        console.error('Error loading posts:', error);
                    }
                });
            }

            function escapeHtml(text) {
                return $('<div>').text(text).html();
            }

            // Load posts on page load
            loadPosts();
        });
    </script>
</body>
</html>

