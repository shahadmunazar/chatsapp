<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile</title>
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
            max-width: 900px;
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
        .header-right {
            display: flex;
            gap: 15px;
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
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .profile-header {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        .avatar-section {
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
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar-upload {
            margin-top: 10px;
        }
        .file-input {
            display: none;
        }
        .upload-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        .remove-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #dc3545;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 5px;
        }
        .profile-info {
            flex: 1;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .posts-section {
            margin-top: 30px;
        }
        .posts-header {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👤 My Profile</h1>
            <div class="header-right">
                <a href="/" class="btn btn-primary">📱 Wall</a>
                <a href="/home" class="btn btn-primary">🏠 Home</a>
                <a href="/chat" class="btn btn-primary">💬 Chat</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-section">
                    <div class="avatar" id="avatarPreview">
                        @if($user->profile_image)
                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}">
                        @else
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        @endif
                    </div>
                    <div class="avatar-upload">
                        <label for="profileImage" class="upload-btn">📷 Upload Photo</label>
                        <input type="file" id="profileImage" class="file-input" accept="image/*">
                        @if($user->profile_image)
                            <button class="remove-btn" onclick="removeImage()">🗑️ Remove</button>
                        @endif
                    </div>
                </div>

                <div class="profile-info">
                    <form method="POST" action="/profile/update">
                        @csrf
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="{{ $user->name }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ $user->email }}" required>
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" rows="3" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-family: inherit;">{{ $user->bio }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="{{ $user->phone }}">
                        </div>

                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ $user->date_of_birth?->format('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px;">
                                <option value="">Select Gender</option>
                                <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
                                <option value="prefer_not_to_say" {{ $user->gender == 'prefer_not_to_say' ? 'selected' : '' }}>Prefer not to say</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="school">School</label>
                            <input type="text" id="school" name="school" value="{{ $user->school }}">
                        </div>

                        <div class="form-group">
                            <label for="college">College/University</label>
                            <input type="text" id="college" name="college" value="{{ $user->college }}">
                        </div>

                        <div class="form-group">
                            <label for="work">Work/Company</label>
                            <input type="text" id="work" name="work" value="{{ $user->work }}">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="{{ $user->address }}">
                        </div>

                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="{{ $user->city }}">
                        </div>

                        <div class="form-group">
                            <label for="state">State/Province</label>
                            <input type="text" id="state" name="state" value="{{ $user->state }}">
                        </div>

                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" value="{{ $user->country }}">
                        </div>

                        <div class="form-group">
                            <label for="website">Website</label>
                            <input type="url" id="website" name="website" value="{{ $user->website }}" placeholder="https://example.com">
                        </div>

                        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                    </form>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $totalUsers }}</div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $onlineUsers }}</div>
                    <div class="stat-label">Online Now</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $totalUsers - $onlineUsers }}</div>
                    <div class="stat-label">Offline</div>
                </div>
            </div>

            <div class="posts-section">
                <div class="posts-header">📝 My Posts</div>
                <div id="postsContainer">
                    <div style="text-align: center; color: #999;">Loading posts...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Handle profile image upload
            $('#profileImage').on('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('profile_image', file);

                $.ajax({
                    url: '/profile/upload-image',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(result) {
                        if (result.success) {
                            location.reload();
                        } else {
                            alert('Failed to upload image');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                        alert('Error uploading image');
                    }
                });
            });

            // Remove profile image (exposed globally for onclick)
            window.removeImage = function() {
                if (!confirm('Are you sure you want to remove your profile image?')) {
                    return;
                }

                $.ajax({
                    url: '/profile/remove-image',
                    type: 'DELETE',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(result) {
                        if (result.success) {
                            location.reload();
                        } else {
                            alert('Failed to remove image');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                        alert('Error removing image');
                    }
                });
            };

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

