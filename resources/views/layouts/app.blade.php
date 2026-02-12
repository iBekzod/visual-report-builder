<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Visual Report Builder')</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>

    <style>
        :root {
            --primary: #f53003;
            --primary-dark: #dc2626;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --border: #e2e8f0;
            --text: #334155;
            --text-muted: #64748b;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f1f5f9;
            color: var(--text);
            font-size: 0.875rem;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Navigation */
        .navbar {
            background: white;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            text-decoration: none;
        }

        .navbar-brand svg {
            width: 2rem;
            height: 2rem;
            color: var(--primary);
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: var(--radius);
            transition: all 0.15s ease;
        }

        .nav-link:hover {
            color: var(--text);
            background-color: var(--light);
        }

        .nav-link.active {
            color: var(--primary);
            background-color: #fef2f2;
        }

        .nav-link svg {
            width: 1.125rem;
            height: 1.125rem;
        }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.025em;
        }

        .page-subtitle {
            margin-top: 0.5rem;
            color: var(--text-muted);
            font-size: 1rem;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--light);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1;
            text-decoration: none;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn svg {
            width: 1rem;
            height: 1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: white;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--light);
            border-color: #cbd5e1;
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
        }

        .btn-ghost:hover {
            background: var(--light);
            color: var(--text);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 0.5rem 0.875rem;
            font-size: 0.8125rem;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text);
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            line-height: 1.5;
            color: var(--text);
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            transition: all 0.15s ease;
            outline: none;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(245, 48, 3, 0.1);
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .table th {
            padding: 0.875rem 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-muted);
            background: var(--light);
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: var(--light);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .alert svg {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 9999px;
        }

        .badge-primary {
            background: #fef2f2;
            color: var(--primary);
        }

        .badge-success {
            background: #ecfdf5;
            color: #059669;
        }

        .badge-warning {
            background: #fffbeb;
            color: #d97706;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: var(--secondary);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .stat-card-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-card-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .stat-card-icon.primary {
            background: #fef2f2;
            color: var(--primary);
        }

        .stat-card-icon.success {
            background: #ecfdf5;
            color: var(--success);
        }

        .stat-card-icon.warning {
            background: #fffbeb;
            color: var(--warning);
        }

        .stat-card-icon.info {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* Grid Layouts */
        .grid {
            display: grid;
            gap: 1.5rem;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        @media (max-width: 1024px) {
            .grid-cols-3, .grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .grid-cols-2, .grid-cols-3, .grid-cols-4 {
                grid-template-columns: 1fr;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
        }

        .empty-state-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.5rem;
            color: #cbd5e1;
        }

        .empty-state-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        /* Dropdown Zones */
        .drop-zone {
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            min-height: 80px;
            background: var(--light);
            transition: all 0.15s ease;
        }

        .drop-zone:hover {
            border-color: #cbd5e1;
        }

        .drop-zone.drag-over {
            border-color: var(--primary);
            background: #fef2f2;
        }

        .drop-zone-text {
            color: var(--text-muted);
            font-size: 0.8125rem;
            text-align: center;
        }

        /* Tags */
        .tag {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            background: var(--primary);
            color: white;
            font-size: 0.8125rem;
            font-weight: 500;
            border-radius: var(--radius);
            margin: 0.25rem;
        }

        .tag-remove {
            background: none;
            border: none;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            padding: 0;
            line-height: 1;
            font-size: 1.125rem;
        }

        .tag-remove:hover {
            color: white;
        }

        /* Modal */
        .modal-overlay,
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .modal-overlay.active,
        .modal-backdrop.active {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow: hidden;
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: var(--radius);
            transition: all 0.15s ease;
        }

        .modal-close:hover {
            background: var(--light);
            color: var(--text);
        }

        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            background: var(--light);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-muted { color: var(--text-muted); }
        .text-primary { color: var(--primary); }
        .text-success { color: var(--success); }
        .text-danger { color: var(--danger); }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 0.5rem; }
        .gap-4 { gap: 1rem; }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="{{ route('visual-reports.dashboard') }}" class="navbar-brand">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3v18h18"/>
                    <path d="M18 17V9"/>
                    <path d="M13 17V5"/>
                    <path d="M8 17v-3"/>
                </svg>
                Report Builder
            </a>
            <div class="navbar-menu">
                <a href="{{ route('visual-reports.dashboard') }}" class="nav-link {{ request()->routeIs('visual-reports.dashboard') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('visual-reports.builder') }}" class="nav-link {{ request()->routeIs('visual-reports.builder') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3v18"/>
                        <path d="M3 12h18"/>
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                    Builder
                </a>
                @if(auth()->check())
                    <span class="nav-link" style="cursor: default;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>
                        </svg>
                        {{ auth()->user()->name ?? 'User' }}
                    </span>
                @endif
            </div>
        </div>
    </nav>

    <main class="main-container">
        @if ($errors->any())
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4"/>
                    <circle cx="12" cy="16" r="1" fill="currentColor"/>
                </svg>
                <div>
                    <strong>Error:</strong>
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M8 12l3 3 5-6"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4"/>
                    <circle cx="12" cy="16" r="1" fill="currentColor"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        window.apiClient = {
            async get(url) {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
                return response.json();
            },

            async post(url, data) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(data),
                });
                return response.json();
            },

            async put(url, data) {
                const response = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(data),
                });
                return response.json();
            },

            async delete(url) {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
                return response.status === 204 ? null : response.json();
            }
        };
    </script>

    @yield('scripts')
</body>
</html>
