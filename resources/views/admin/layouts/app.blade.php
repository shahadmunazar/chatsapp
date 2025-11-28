<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Dashboard') | {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('admin/images/favicon.svg') }}" type="image/x-icon" />
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('admin/fonts/phosphor/duotone/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/fonts/material.css') }}" />
    
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/style.css') }}" id="main-style-link" />
    
    @stack('styles')
</head>

<body>
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg fixed inset-0 bg-white dark:bg-themedark-cardbg z-[1034]">
        <div class="loader-track h-[5px] w-full inline-block absolute overflow-hidden top-0">
            <div class="loader-fill w-[300px] h-[5px] bg-primary-500 absolute top-0 left-0 animate-[hitZak_0.6s_ease-in-out_infinite_alternate]"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    
    @include('admin.layouts.sidebar')
    
    @include('admin.layouts.header')
    
    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    <!-- [ Main Content ] end -->
    
    <!-- Required Js -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="{{ asset('admin/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('admin/js/icon/custom-icon.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('admin/js/component.js') }}"></script>
    <script src="{{ asset('admin/js/theme.js') }}"></script>
    <script src="{{ asset('admin/js/script.js') }}"></script>
    <script src="{{ asset('admin/js/admin-ajax.js') }}"></script>
    
    @stack('scripts')
</body>
</html>

