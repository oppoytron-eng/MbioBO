<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard')</title>
    <style>
        :root {
            --bg: #020617;
            --card: #0f172a;
            --accent: #38bdf8;
            --muted: rgba(255,255,255,0.6);
        }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, sans-serif;
            background: radial-gradient(circle at top, rgba(56,189,248,0.2), transparent 45%), #020617;
            color: white;
            min-height: 100vh;
        }
        header {
            padding: 1.5rem 2rem;
            background: linear-gradient(90deg, rgba(15,23,42,0.95), rgba(15,23,42,0.6));
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        header h1 {
            margin: 0;
            font-size: 1.75rem;
            letter-spacing: 0.05em;
        }
        .layout {
            padding: 2rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .card {
            background: var(--card);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 20px 60px rgba(15,23,42,0.45);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
        }
        .card small {
            color: var(--muted);
        }
        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }
        .actions form button {
            width: 100%;
            padding: 0.85rem;
            border-radius: 0.75rem;
            border: none;
            font-weight: 600;
            cursor: pointer;
            background: var(--accent);
            color: #020617;
        }
        .status {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            background: rgba(14, 165, 233, 0.15);
            color: var(--accent);
        }
        .header-grid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .header-grid nav {
            display: flex;
            gap: 0.75rem;
        }
        .header-grid nav a {
            border-radius: 999px;
            padding: 0.55rem 1.2rem;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.05);
            color: inherit;
            font-size: 0.9rem;
            transition: background 0.2s ease;
        }
        .header-grid nav a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .header-grid nav a.active {
            background: var(--accent);
            color: #020617;
            font-weight: 700;
        }
        .filters {
            background: var(--card);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }
        .filters input,
        .filters button,
        .filters label {
            font-size: 0.9rem;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        th, td {
            padding: 0.75rem 0.5rem;
            text-align: left;
        }
        tbody tr {
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .muted {
            color: var(--muted);
        }
        .badge.success {
            background: rgba(16, 185, 129, 0.15);
            color: #5EEAD4;
        }
        .badge.warn {
            background: rgba(248, 113, 113, 0.15);
            color: #FCA5A5;
        }
        .quick-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin: 0.5rem 0;
        }
        .quick-actions .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .quick-actions form {
            margin: 0;
        }
        .quick-actions form button {
            border: none;
            background: none;
            padding: 0;
            color: inherit;
            font: inherit;
            cursor: pointer;
        }
        .pill {
            border-radius: 999px;
            padding: 0.25rem 0.9rem;
            border: 1px solid rgba(255,255,255,0.15);
            font-size: 0.8rem;
        }
        .pagination {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            margin-top: 1rem;
        }
        .pagination a {
            padding: 0.4rem 0.8rem;
            border-radius: 0.75rem;
            color: white;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.05);
        }
        .pagination span {
            padding: 0.4rem 0.8rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255,255,255,0.2);
        }
        footer {
            text-align: center;
            padding: 1rem;
            font-size: 0.85rem;
            color: var(--muted);
        }
    </style>
</head>
<body>
<header>
    <div class="header-grid">
        <div>
            <h1>Bienvenue Admin</h1>
            <p>Surveillez les chauffeurs, les clients et les courses en temps réel.</p>
        </div>
        <nav>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Tableau de bord</a>
            <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">Clients</a>
            <a href="{{ route('admin.courses.index') }}" class="{{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">Courses</a>
            <a href="{{ route('admin.chauffeurs.index') }}" class="{{ request()->routeIs('admin.chauffeurs.*') ? 'active' : '' }}">Chauffeurs</a>
            <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">Notifications</a>
            <a href="{{ route('admin.finances.transactions') }}" class="{{ request()->routeIs('admin.finances.*') ? 'active' : '' }}">Finances</a>
            <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">Rôles</a>
            <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Paramètres</a>
        </nav>
    </div>
</header>
<main class="layout">
    @if (session('status'))
        <div class="status">
            {{ session('status') }}
        </div>
    @endif

    @yield('content')

    <footer>Powered by MBIO Back Office · Guard Group2</footer>
    @yield('scripts')
</main>
</body>
</html>
