<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMGOS Dynamic Report')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script>
        (function () {
            try {
                if (window.innerWidth > 820 && window.localStorage.getItem('simgos-sidebar-collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed-preload');
                }
            } catch (error) {
                // Ignore storage restrictions and let the regular sidebar behavior continue.
            }
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ai-chat.css') }}">
    @stack('head')
</head>

<body>
    <div class="app-shell" data-app-shell>
        @include('partials.sidebar')

        <div class="main-shell">
            <header class="topbar">
                <div class="topbar-leading">
                    <button type="button" class="icon-button mobile-menu-button" data-sidebar-toggle
                        aria-label="Buka menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <button type="button" class="icon-button sidebar-collapse topbar-sidebar-toggle"
                        data-sidebar-collapse aria-label="Minimalkan sidebar" aria-expanded="true"
                        title="Minimalkan sidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="topbar-context">
                        <span class="topbar-title">SIMRS Reporting Platform</span>
                        {{-- <span class="topbar-title">SIMGOS Dynamic Report</span> --}}
                    </div>
                </div>
                <div class="topbar-actions">
                    <span class="connection-pill"><span></span> Read-only mode</span>
                    {{-- <div class="avatar">SG</div> --}}
                </div>
            </header>

            <main class="page-content">
                @if (session('success'))
                    <div class="flash flash-success"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}
                    </div>
                @endif
                @if (session('info'))
                    <div class="flash flash-info"><i class="fa-solid fa-circle-info"></i>{{ session('info') }}</div>
                @endif
                @if ($errors->any())
                    <div class="flash flash-error"><i
                            class="fa-solid fa-triangle-exclamation"></i><span>{{ $errors->first() }}</span></div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <div class="sidebar-overlay" data-sidebar-overlay></div>
    @include('components.ai-chat-widget')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    @stack('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/ai-chat.js') }}"></script>
</body>

</html>
