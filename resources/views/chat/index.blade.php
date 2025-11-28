<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Real-Time Chat</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .chat-container {
            width: 90%;
            max-width: 1200px;
            height: 80vh;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            display: flex;
            overflow: hidden;
        }
        .users-sidebar {
            width: 300px;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 20px;
            background: #667eea;
            color: white;
        }
        .sidebar-header h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .current-user {
            font-size: 14px;
            opacity: 0.9;
        }
        .users-list {
            flex: 1;
            overflow-y: auto;
        }
        .user-item {
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 1px solid #e9ecef;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .chat-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            flex-shrink: 0;
            overflow: hidden;
        }
        .chat-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-details {
            flex: 1;
            min-width: 0;
        }
        .user-item:hover {
            background: #e9ecef;
        }
        .user-item.active {
            background: #667eea;
            color: white;
        }
        .user-name {
            font-weight: 600;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-email {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 2px;
        }
        .last-message {
            font-size: 13px;
            opacity: 0.8;
            margin-top: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .unread-badge {
            background: #FF4433;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
        }
        .user-item.active .unread-badge {
            background: white;
            color: #667eea;
        }
        .user-status {
            display: flex;
            align-items: center;
            font-size: 12px;
            margin-top: 4px;
            opacity: 0.8;
        }
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            display: inline-block;
        }
        .status-online {
            background: #28a745;
            box-shadow: 0 0 4px #28a745;
        }
        .status-offline {
            background: #6c757d;
        }
        .user-item.active .user-status {
            opacity: 1;
        }
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 20px;
            background: white;
            border-bottom: 1px solid #dee2e6;
        }
        .chat-header h3 {
            font-size: 18px;
            color: #333;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }
        .no-chat-selected {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
            font-size: 18px;
        }
        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        .message.sent {
            align-items: flex-end;
        }
        .message.received {
            align-items: flex-start;
        }
        .message-bubble {
            max-width: 60%;
            padding: 12px 16px;
            border-radius: 16px;
            word-wrap: break-word;
        }
        .message.sent .message-bubble {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 4px;
        }
        .message.received .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message-meta {
            font-size: 11px;
            margin-top: 4px;
            opacity: 0.7;
        }
        .message.sent .message-meta {
            text-align: right;
        }
        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #dee2e6;
        }
        .chat-input-form {
            display: flex;
            gap: 10px;
        }
        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 24px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .chat-input:focus {
            border-color: #667eea;
        }
        .send-button {
            padding: 12px 32px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }
        .send-button:hover {
            background: #5568d3;
        }
        .send-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .status-badge {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #28a745;
            margin-right: 8px;
        }
        .loading {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        .file-attachment {
            margin-top: 8px;
            padding: 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            max-width: 300px;
        }
        .message.sent .file-attachment {
            background: rgba(255,255,255,0.2);
        }
        .message.received .file-attachment {
            background: #f8f9fa;
        }
        .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .file-icon {
            font-size: 32px;
        }
        .file-details {
            flex: 1;
            min-width: 0;
        }
        .file-name {
            font-weight: 600;
            font-size: 13px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .file-size {
            font-size: 11px;
            opacity: 0.7;
        }
        .file-download {
            padding: 6px 12px;
            background: rgba(255,255,255,0.3);
            border-radius: 6px;
            text-decoration: none;
            color: inherit;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .message.sent .file-download {
            background: rgba(255,255,255,0.3);
            color: white;
        }
        .message.received .file-download {
            background: #667eea;
            color: white;
        }
        .file-download:hover {
            transform: scale(1.05);
        }
        .file-image {
            max-width: 300px;
            max-height: 300px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 8px;
        }
        .file-video {
            max-width: 300px;
            border-radius: 8px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Users Sidebar -->
        <div class="users-sidebar">
            <div class="sidebar-header">
                <h2>💬 Chat</h2>
                <div class="current-user">
                    <span class="status-badge"></span>
                    {{ Auth::user()->name }}
                </div>
                <a href="/" style="display: block; margin-top: 10px; text-align: center; background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 12px;">
                    📱 Wall
                </a>
                <a href="/home" style="display: block; margin-top: 8px; text-align: center; background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 12px;">
                    🏠 Home
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin-top: 8px;">
                    @csrf
                    <button type="submit" style="background: rgba(255,255,255,0.2); color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; width: 100%;">
                        🚪 Logout
                    </button>
                </form>
            </div>
            <div class="users-list" id="usersList">
                <div class="loading">Loading users...</div>
            </div>
        </div>

        <!-- Chat Main Area -->
        <div class="chat-main">
            <div class="chat-header" id="chatHeader" style="display: none;">
                <h3 id="chatHeaderName">Select a user to start chatting</h3>
            </div>
            <div class="chat-messages" id="chatMessages">
                <div class="no-chat-selected">
                    👈 Select a user from the sidebar to start chatting
                </div>
            </div>
            <div class="chat-input-container" id="chatInputContainer" style="display: none;">
                <div id="filePreview" style="display: none; padding: 10px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span id="fileIcon" style="font-size: 24px;">📎</span>
                            <div>
                                <div id="fileName" style="font-weight: 600; font-size: 14px;"></div>
                                <div id="fileSize" style="font-size: 12px; color: #666;"></div>
                            </div>
                        </div>
                        <button onclick="clearFile()" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer;">✖️ Remove</button>
                    </div>
                </div>
                <form class="chat-input-form" id="messageForm" enctype="multipart/form-data">
                    <input 
                        type="file" 
                        id="fileInput" 
                        accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar"
                        style="display: none;"
                    >
                    <button type="button" onclick="$('#fileInput').click()" style="padding: 12px 16px; background: #6c757d; color: white; border: none; border-radius: 24px; cursor: pointer; font-size: 18px;">📎</button>
                    <input 
                        type="text" 
                        class="chat-input" 
                        id="messageInput" 
                        placeholder="Type your message or attach a file..."
                        autocomplete="off"
                    >
                    <button type="submit" class="send-button">Send</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Configuration
            const currentUserId = {{ Auth::id() }};
            const currentUserName = "{{ Auth::user()->name }}";
            let selectedUser = null;
            let pusher = null;
            let channel = null;

            // CSRF token setup
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Initialize Pusher/Reverb
        function initializePusher() {
            @if(config('broadcasting.default') === 'reverb')
            pusher = new Pusher('{{ config('broadcasting.connections.reverb.key') }}', {
                wsHost: '{{ config('reverb.servers.reverb.hostname', 'localhost') }}',
                wsPort: {{ config('reverb.servers.reverb.port', 8080) }},
                wssPort: {{ config('reverb.servers.reverb.port', 8080) }},
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],
                cluster: 'mt1',
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                }
            });
            @else
            pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                }
            });
            @endif

            // Subscribe to current user's private channel
            channel = pusher.subscribe('private-chat.' + currentUserId);
            
            channel.bind('message.sent', function(data) {
                if (selectedUser && data.sender_id === selectedUser.id) {
                    displayMessage(data, 'received');
                }
                // Reload users list to update last message and unread count
                loadUsers();
            });

            console.log('Pusher initialized and subscribed to private-chat.' + currentUserId);
        }

            // Load users
            function loadUsers() {
                $.ajax({
                    url: '/chat/users',
                    type: 'GET',
                    dataType: 'json',
                    success: function(users) {
                        if (users.length === 0) {
                            $('#usersList').html('<div class="loading">No other users available</div>');
                            return;
                        }
                        
                        const usersHtml = users.map(user => {
                            const unreadBadge = user.unread_count > 0 
                                ? `<span class="unread-badge">${user.unread_count}</span>` 
                                : '';
                            
                            const lastMessageText = user.last_message 
                                ? `<div class="last-message">${truncateText(user.last_message, 40)}</div>` 
                                : '';
                            
                            const statusClass = user.is_online ? 'status-online' : 'status-offline';
                            const statusText = user.last_seen || 'Offline';
                            
                            const initials = user.name.split(' ').map(n => n[0]).join('').substring(0, 2);
                            const avatarContent = user.profile_image 
                                ? `<img src="${user.profile_image}" alt="${user.name}">`
                                : initials;
                            
                            return `
                                <div class="user-item" data-user-id="${user.id}" data-user-name="${user.name}">
                                    <div class="chat-avatar">${avatarContent}</div>
                                    <div class="user-details">
                                        <div class="user-name">
                                            <span>${user.name}</span>
                                            ${unreadBadge}
                                        </div>
                                        <div class="user-email">${user.email}</div>
                                        <div class="user-status">
                                            <span class="status-indicator ${statusClass}"></span>
                                            <span>${statusText}</span>
                                        </div>
                                        ${lastMessageText}
                                    </div>
                                </div>
                            `;
                        }).join('');

                        $('#usersList').html(usersHtml);
                    },
                    error: function(error) {
                        console.error('Error loading users:', error);
                        $('#usersList').html('<div class="loading">Error loading users</div>');
                    }
                });
            }

        // Truncate text helper
        function truncateText(text, maxLength) {
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }

            // Load chat history with a user
            function loadChatHistory(userId) {
                $.ajax({
                    url: `/chat/history/${userId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(messages) {
                        $('#chatMessages').empty();
                        
                        if (messages.length === 0) {
                            $('#chatMessages').html('<div style="text-align: center; padding: 20px; color: #6c757d;">No messages yet. Start the conversation!</div>');
                            return;
                        }
                        
                        messages.forEach(function(message) {
                            const isSent = message.sender_id === currentUserId;
                            displayMessage({
                                sender_id: message.sender_id,
                                sender_name: message.sender.name,
                                message: message.message,
                                timestamp: message.created_at,
                                file_url: message.file_url,
                                file_name: message.file_name,
                                file_type: message.file_type,
                                file_size: message.file_size
                            }, isSent ? 'sent' : 'received', false);
                        });
                        
                        // Scroll to bottom
                        const container = $('#chatMessages')[0];
                        container.scrollTop = container.scrollHeight;
                    },
                    error: function(error) {
                        console.error('Error loading chat history:', error);
                    }
                });
            }

            // Select a user to chat with
            function selectUser(element) {
                // Remove active class from all users
                $('.user-item').removeClass('active');
                
                // Add active class to selected user
                $(element).addClass('active');
                
                // Store selected user info
                selectedUser = {
                    id: parseInt($(element).data('user-id')),
                    name: $(element).data('user-name')
                };
                
                // Update UI
                $('#chatHeader').show();
                $('#chatHeaderName').text(selectedUser.name);
                $('#chatInputContainer').show();
                
                // Load chat history
                loadChatHistory(selectedUser.id);
                
                // Reload users list to update unread counts
                loadUsers();
            }

            // Display a message
            function displayMessage(data, type, autoScroll = true) {
                const $messagesContainer = $('#chatMessages');
                
                // Remove "no messages" text if it exists
                if ($messagesContainer.find('.no-chat-selected').length ||
                    $messagesContainer.text().includes('Start your conversation') ||
                    $messagesContainer.text().includes('No messages yet')) {
                    $messagesContainer.empty();
                }
                
                const time = new Date(data.timestamp || Date.now()).toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Build file attachment HTML if file exists
                let fileHtml = '';
                if (data.file_url) {
                    const fileIcon = getFileIcon(data.file_type || '', data.file_name || '');
                    const fileSize = formatFileSize(data.file_size || 0);
                    
                    if (data.file_type === 'image') {
                        fileHtml = `
                            <div class="file-attachment">
                                <img src="${data.file_url}" class="file-image" alt="${data.file_name}" onclick="window.open('${data.file_url}', '_blank')">
                                <div style="margin-top: 8px; font-size: 12px; opacity: 0.8;">${data.file_name}</div>
                            </div>
                        `;
                    } else if (data.file_type === 'video') {
                        fileHtml = `
                            <div class="file-attachment">
                                <video controls class="file-video">
                                    <source src="${data.file_url}" type="video/mp4">
                                    Your browser does not support video playback.
                                </video>
                                <div style="margin-top: 8px; font-size: 12px; opacity: 0.8;">${data.file_name}</div>
                            </div>
                        `;
                    } else {
                        fileHtml = `
                            <div class="file-attachment">
                                <div class="file-info">
                                    <div class="file-icon">${fileIcon}</div>
                                    <div class="file-details">
                                        <div class="file-name">${data.file_name}</div>
                                        <div class="file-size">${fileSize}</div>
                                    </div>
                                    <a href="${data.file_url}" download="${data.file_name}" class="file-download">📥 Download</a>
                                </div>
                            </div>
                        `;
                    }
                }
                
                const messageHtml = `
                    <div class="message ${type}">
                        <div class="message-bubble">
                            ${type === 'received' ? `<strong>${data.sender_name}:</strong><br>` : ''}
                            ${data.message}
                            ${fileHtml}
                        </div>
                        <div class="message-meta">
                            ${time}
                        </div>
                    </div>
                `;
                
                $messagesContainer.append(messageHtml);
                
                if (autoScroll) {
                    $messagesContainer[0].scrollTop = $messagesContainer[0].scrollHeight;
                }
            }

            // Format file size
            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            }

            // File selection handler
            let selectedFile = null;
            $('#fileInput').on('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Check file size (10MB limit)
                const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                if (file.size > maxSize) {
                    alert('File size must be less than 10MB');
                    $(this).val('');
                    return;
                }

                selectedFile = file;
                const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                if (file.size > 1024 * 1024) {
                    fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                }

                // Get file icon
                const icon = getFileIcon(file.type, file.name);

                // Show preview
                $('#fileIcon').text(icon);
                $('#fileName').text(file.name);
                $('#fileSize').text(fileSize);
                $('#filePreview').show();
            });

            // Clear file
            window.clearFile = function() {
                selectedFile = null;
                $('#fileInput').val('');
                $('#filePreview').hide();
            };

            // Get file icon
            function getFileIcon(mimeType, fileName) {
                if (mimeType.startsWith('image/')) return '🖼️';
                if (mimeType.startsWith('video/')) return '🎬';
                if (mimeType.includes('pdf')) return '📄';
                if (mimeType.includes('word') || fileName.endsWith('.doc') || fileName.endsWith('.docx')) return '📝';
                if (mimeType.includes('text')) return '📃';
                if (mimeType.includes('zip') || mimeType.includes('rar')) return '📦';
                return '📎';
            }

            // Send message
            $('#messageForm').on('submit', function(e) {
                e.preventDefault();
                
                if (!selectedUser) {
                    alert('Please select a user to chat with');
                    return;
                }
                
                const message = $('#messageInput').val().trim();
                
                if (!message && !selectedFile) {
                    alert('Please enter a message or select a file');
                    return;
                }

                const formData = new FormData();
                formData.append('receiver_id', selectedUser.id);
                if (message) {
                    formData.append('message', message);
                }
                if (selectedFile) {
                    formData.append('file', selectedFile);
                }
                
                $.ajax({
                    url: '/chat/send',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(result) {
                        if (result.success) {
                            // Display sent message
                            displayMessage({
                                sender_name: currentUserName,
                                message: result.data.message,
                                timestamp: result.data.created_at,
                                file_url: result.data.file_url,
                                file_name: result.data.file_name,
                                file_type: result.data.file_type,
                                file_size: result.data.file_size
                            }, 'sent');
                            
                            // Clear input
                            $('#messageInput').val('');
                            clearFile();
                            
                            // Reload users list to update last message
                            loadUsers();
                        } else {
                            alert('Failed to send message');
                        }
                    },
                    error: function(error) {
                        console.error('Error sending message:', error);
                        alert('Error sending message');
                    }
                });
            });

            // Update user activity (heartbeat)
            function updateActivity() {
                $.ajax({
                    url: '/chat/activity',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    error: function(error) {
                        console.error('Error updating activity:', error);
                    }
                });
            }

            // Event delegation for user item clicks
            $(document).on('click', '.user-item', function() {
                selectUser(this);
            });

            // Heartbeat to keep user online (update every 2 minutes)
            setInterval(updateActivity, 2 * 60 * 1000);

            // Update user list every 30 seconds to refresh online status
            setInterval(function() {
                if (!document.hidden) {
                    loadUsers();
                }
            }, 30 * 1000);

            // Initialize on page load
            initializePusher();
            loadUsers();
            updateActivity();

            // Update activity when page becomes visible
            $(document).on('visibilitychange', function() {
                if (!document.hidden) {
                    updateActivity();
                    loadUsers();
                }
            });
        });
    </script>
</body>
</html>

