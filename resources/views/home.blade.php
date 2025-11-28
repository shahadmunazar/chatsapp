<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Home - Discover People</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
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
            max-width: 1400px;
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
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
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
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-success {
            background: #28a745;
            color: white;
            font-size: 14px;
            padding: 8px 16px;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
            font-size: 14px;
            padding: 8px 16px;
        }
        .badge {
            background: #667eea;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            position: relative;
            top: -2px;
        }
        .badge-warning {
            background: #ffc107;
            color: #333;
        }

        .tabs {
            background: white;
            border-radius: 16px 16px 0 0;
            padding: 0;
            display: flex;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .tab {
            flex: 1;
            padding: 18px;
            text-align: center;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            font-weight: 600;
            color: #666;
            font-size: 16px;
        }
        .tab.active {
            border-bottom-color: #667eea;
            color: #667eea;
        }
        .tab:hover {
            background: #f8f9fa;
        }
        .content {
            background: white;
            border-radius: 0 0 16px 16px;
            padding: 40px;
            min-height: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }

        /* User Slider Styles */
        .slider-container {
            position: relative;
            padding: 20px 60px;
        }
        .user-slider {
            margin: 0 auto;
        }
        .profile-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            transition: all 0.3s;
            min-height: 450px;
            display: flex !important;
            flex-direction: column;
        }
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 40px;
            margin: 0 auto 15px;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .profile-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-name {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .profile-email {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .profile-status {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-online {
            background: #28a745;
            box-shadow: 0 0 6px #28a745;
        }
        .status-offline {
            background: #6c757d;
        }
        .profile-details {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            flex-grow: 1;
        }
        .detail-item {
            display: flex;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-icon {
            font-size: 18px;
            margin-right: 12px;
            min-width: 25px;
        }
        .detail-content {
            flex: 1;
        }
        .detail-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .detail-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        .profile-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }
        .profile-actions button,
        .profile-actions a {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
        }

        /* Slick Slider Custom Controls */
        .slick-prev, .slick-next {
            width: 50px;
            height: 50px;
            z-index: 1;
        }
        .slick-prev {
            left: 0;
        }
        .slick-next {
            right: 0;
        }
        .slick-prev:before, .slick-next:before {
            font-size: 50px;
            opacity: 0.75;
            color: #667eea;
        }
        .slick-prev:hover:before, .slick-next:hover:before {
            opacity: 1;
        }
        .slick-dots {
            bottom: -40px;
        }
        .slick-dots li button:before {
            font-size: 12px;
            color: #667eea;
        }

        /* Friend Requests */
        .request-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            background: #fff;
            transition: all 0.2s;
        }
        .request-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .request-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
            overflow: hidden;
        }
        .request-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .request-info {
            flex: 1;
        }
        .user-name {
            font-weight: 600;
            font-size: 18px;
            color: #333;
        }
        .user-email {
            font-size: 14px;
            color: #666;
            margin-top: 3px;
        }
        .request-time {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .request-actions {
            display: flex;
            gap: 10px;
        }
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #999;
        }
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .loading {
            text-align: center;
            padding: 60px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Discover People</h1>
            <div class="header-right">
                <span style="color: #666;">Welcome, <strong>{{ Auth::user()->name }}</strong></span>
                <a href="/profile" class="btn btn-primary">👤 Profile</a>
                <a href="/chat" class="btn btn-primary">💬 Chat</a>
                <a href="/" class="btn btn-primary">📱 Wall</a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary">🚪 Logout</button>
                </form>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active" data-tab="discover">
                🔍 Discover People
            </div>
            <div class="tab" data-tab="requests">
                📬 Friend Requests <span id="requestsBadge" class="badge badge-warning" style="display: none;">0</span>
            </div>
        </div>

        <div class="content">
            <!-- Discover People Tab -->
            <div id="discoverTab" class="tab-content active">
                <div class="slider-container">
                    <div class="user-slider" id="userSlider">
                        <div class="loading">
                            <div style="font-size: 40px; margin-bottom: 15px;">⏳</div>
                            <div>Loading amazing people...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Friend Requests Tab -->
            <div id="requestsTab" class="tab-content">
                <h2 style="margin-bottom: 25px; color: #333;">📬 Pending Friend Requests</h2>
                <div id="requestsList">
                    <div class="loading">Loading requests...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        let sliderInitialized = false;

        // Tab switching
        $('.tab').on('click', function() {
            const tabName = $(this).data('tab');

            $('.tab').removeClass('active');
            $(this).addClass('active');

            $('.tab-content').removeClass('active');

            if (tabName === 'discover') {
                $('#discoverTab').addClass('active');
            } else if (tabName === 'requests') {
                $('#requestsTab').addClass('active');
                loadFriendRequests();
            }
        });

        // Load all users for slider
        function loadUsers() {
            $.ajax({
                url: '/friends/all',
                type: 'GET',
                dataType: 'json',
                success: function(users) {
                    if (users.length === 0) {
                        $('#userSlider').html(`
                            <div class="empty-state">
                                <div class="empty-state-icon">😊</div>
                                <h3>No other users yet</h3>
                                <p>Be the first to invite your friends!</p>
                            </div>
                        `);
                        return;
                    }

                    const slides = users.map(user => createProfileCard(user)).join('');
                    $('#userSlider').html(slides);

                    // Initialize Slick Slider
                    if (!sliderInitialized) {
                        $('#userSlider').slick({
                            slidesToShow: 3,
                            slidesToScroll: 1,
                            autoplay: false,
                            autoplaySpeed: 3000,
                            dots: true,
                            arrows: true,
                            responsive: [
                                {
                                    breakpoint: 1200,
                                    settings: {
                                        slidesToShow: 2
                                    }
                                },
                                {
                                    breakpoint: 768,
                                    settings: {
                                        slidesToShow: 1
                                    }
                                }
                            ]
                        });
                        sliderInitialized = true;
                    } else {
                        $('#userSlider').slick('unslick');
                        $('#userSlider').html(slides);
                        $('#userSlider').slick({
                            slidesToShow: 3,
                            slidesToScroll: 1,
                            autoplay: false,
                            autoplaySpeed: 3000,
                            dots: true,
                            arrows: true,
                            responsive: [
                                {
                                    breakpoint: 1200,
                                    settings: {
                                        slidesToShow: 2
                                    }
                                },
                                {
                                    breakpoint: 768,
                                    settings: {
                                        slidesToShow: 1
                                    }
                                }
                            ]
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading users:', error);
                    $('#userSlider').html(`
                        <div class="empty-state">
                            <div class="empty-state-icon">❌</div>
                            <h3>Error loading users</h3>
                            <p>Please refresh the page to try again.</p>
                        </div>
                    `);
                }
            });
        }

        // Create profile card HTML
        function createProfileCard(user) {
            const initials = user.name.split(' ').map(n => n[0]).join('').substring(0, 2);
            const avatarContent = user.profile_image
                ? `<img src="${user.profile_image}" alt="${user.name}">`
                : initials;

            const statusClass = user.is_online ? 'status-online' : 'status-offline';
            const statusText = user.last_seen || 'Offline';

            // Build details
            const details = [];
            if (user.bio) details.push({ icon: '📝', label: 'Bio', value: truncate(user.bio, 60) });
            if (user.city) details.push({ icon: '📍', label: 'City', value: user.city });
            if (user.school) details.push({ icon: '🎓', label: 'School', value: user.school });
            if (user.work) details.push({ icon: '💼', label: 'Work', value: user.work });

            const detailsHtml = details.length > 0
                ? details.map(d => `
                    <div class="detail-item">
                        <div class="detail-icon">${d.icon}</div>
                        <div class="detail-content">
                            <div class="detail-label">${d.label}</div>
                            <div class="detail-value">${d.value}</div>
                        </div>
                    </div>
                `).join('')
                : `<div style="text-align: center; padding: 20px; color: #999;">No additional details</div>`;

            let actionButtons = '';
            if (user.friendship_status === 'none') {
                actionButtons = `
                    <button class="btn btn-primary" onclick="sendFriendRequest(${user.id})">➕ Add Friend</button>
                    <a href="/profile/${user.id}" class="btn btn-secondary">👤 View Profile</a>
                `;
            } else if (user.friendship_status === 'sent') {
                actionButtons = `
                    <button class="btn btn-secondary" disabled>✓ Request Sent</button>
                    <button class="btn btn-danger" onclick="cancelRequest(${user.request_id})">✖️ Cancel</button>
                `;
            } else if (user.friendship_status === 'received') {
                actionButtons = `
                    <button class="btn btn-success" onclick="acceptRequest(${user.request_id})">✓ Accept</button>
                    <button class="btn btn-danger" onclick="rejectRequest(${user.request_id})">✖️ Reject</button>
                `;
            } else if (user.friendship_status === 'friends') {
                actionButtons = `
                    <a href="/profile/${user.id}" class="btn btn-primary">👤 View Profile</a>
                    <a href="/chat" class="btn btn-success">💬 Chat</a>
                `;
            }

            return `
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar-large">${avatarContent}</div>
                        <div class="profile-name">${user.name}</div>
                        <div class="profile-email">${user.email}</div>
                        <div class="profile-status">
                            <span class="status-indicator ${statusClass}"></span>
                            <span>${statusText}</span>
                        </div>
                    </div>
                    
                    <div class="profile-details">
                        ${detailsHtml}
                    </div>

                    <div class="profile-actions">
                        ${actionButtons}
                    </div>
                </div>
            `;
        }

        // Truncate text
        function truncate(text, maxLength) {
            if (!text || text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }

        // Load friend requests
        function loadFriendRequests() {
            $.ajax({
                url: '/friends/requests',
                type: 'GET',
                dataType: 'json',
                success: function(requests) {
                    const badge = $('#requestsBadge');

                    if (requests.length > 0) {
                        badge.show().text(requests.length);
                    } else {
                        badge.hide();
                    }

                    if (requests.length === 0) {
                        $('#requestsList').html(`
                            <div class="empty-state">
                                <div class="empty-state-icon">📭</div>
                                <h3>No pending friend requests</h3>
                                <p>When someone sends you a friend request, it will appear here.</p>
                            </div>
                        `);
                        return;
                    }

                    const requestsHtml = requests.map(request => {
                        const initials = request.sender.name.split(' ').map(n => n[0]).join('').substring(0, 2);
                        const avatarContent = request.sender.profile_image
                            ? `<img src="${request.sender.profile_image}" alt="${request.sender.name}">`
                            : initials;

                        return `
                            <div class="request-card">
                                <div class="request-avatar">${avatarContent}</div>
                                <div class="request-info">
                                    <div class="user-name">${request.sender.name}</div>
                                    <div class="user-email">${request.sender.email}</div>
                                    <div class="request-time">📅 ${request.created_at}</div>
                                </div>
                                <div class="request-actions">
                                    <button class="btn btn-success" onclick="acceptRequest(${request.id})">✓ Accept</button>
                                    <button class="btn btn-danger" onclick="rejectRequest(${request.id})">✖️ Reject</button>
                                </div>
                            </div>
                        `;
                    }).join('');
                    $('#requestsList').html(requestsHtml);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading requests:', error);
                    $('#requestsList').html(`
                        <div class="empty-state">
                            <div class="empty-state-icon">❌</div>
                            <h3>Error loading requests</h3>
                        </div>
                    `);
                }
            });
        }

        // Send friend request
        window.sendFriendRequest = function(userId) {
            $.ajax({
                url: '/friends/send',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                data: JSON.stringify({ receiver_id: userId }),
                success: function(result) {
                    if (result.success) {
                        loadUsers();
                        alert('✅ Friend request sent!');
                    } else {
                        alert('❌ ' + result.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('❌ Failed to send friend request');
                }
            });
        };

        // Accept friend request
        window.acceptRequest = function(requestId) {
            $.ajax({
                url: `/friends/accept/${requestId}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(result) {
                    if (result.success) {
                        loadUsers();
                        loadFriendRequests();
                        alert('✅ Friend request accepted!');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('❌ Failed to accept request');
                }
            });
        };

        // Reject friend request
        window.rejectRequest = function(requestId) {
            $.ajax({
                url: `/friends/reject/${requestId}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(result) {
                    if (result.success) {
                        loadUsers();
                        loadFriendRequests();
                        alert('❌ Friend request rejected');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('❌ Failed to reject request');
                }
            });
        };

        // Cancel sent request
        window.cancelRequest = function(requestId) {
            $.ajax({
                url: `/friends/cancel/${requestId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(result) {
                    if (result.success) {
                        loadUsers();
                        alert('🔄 Friend request cancelled');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('❌ Failed to cancel request');
                }
            });
        };

        // Initial load
        loadUsers();
        loadFriendRequests();

        // Refresh every 30 seconds
        setInterval(() => {
            loadUsers();
            loadFriendRequests();
        }, 30000);
    });
    </script>
</body>
</html>
