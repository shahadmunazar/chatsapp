<!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header flex items-center py-4 px-6 h-header-height">
            <a href="{{ route('admin.dashboard') }}" class="b-brand flex items-center gap-3">
                <span class="text-xl font-bold text-white">{{ config('app.name') }}</span>
            </a>
        </div>
        <div class="navbar-content h-[calc(100vh_-_74px)] py-2.5">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>Navigation</label>
                </li>
                <li class="pc-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="pc-link">
                        <span class="pc-micon">
                            <i data-feather="home"></i>
                        </span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>
                
                <li class="pc-item pc-caption">
                    <label>Management</label>
                    <i data-feather="users"></i>
                </li>
                <li class="pc-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="pc-link">
                        <span class="pc-micon">
                            <i data-feather="users"></i>
                        </span>
                        <span class="pc-mtext">Users</span>
                    </a>
                </li>
                <li class="pc-item {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.posts.index') }}" class="pc-link">
                        <span class="pc-micon">
                            <i data-feather="file-text"></i>
                        </span>
                        <span class="pc-mtext">Posts</span>
                    </a>
                </li>
                
                <li class="pc-item pc-caption">
                    <label>Frontend</label>
                    <i data-feather="globe"></i>
                </li>
                <li class="pc-item">
                    <a href="{{ url('/') }}" target="_blank" class="pc-link">
                        <span class="pc-micon">
                            <i data-feather="home"></i>
                        </span>
                        <span class="pc-mtext">Visit Site</span>
                    </a>
                </li>
                <li class="pc-item">
                    <a href="{{ route('home') }}" target="_blank" class="pc-link">
                        <span class="pc-micon">
                            <i data-feather="user"></i>
                        </span>
                        <span class="pc-mtext">User Home</span>
                    </a>
                </li>
                <li class="pc-item">
                    <a href="{{ route('chat') }}" target="_blank" class="pc-link">
                        <span class="pc-micon">
                            <i data-feather="message-circle"></i>
                        </span>
                        <span class="pc-mtext">Chat</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- [ Sidebar Menu ] end -->

