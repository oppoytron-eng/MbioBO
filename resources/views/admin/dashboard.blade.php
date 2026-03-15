@extends('admin.layout')

@section('title', 'Dashboard admin')

@section('content')
    <div class="grid">
        <article class="card">
            <small>Chauffeurs</small>
            <h2>{{ number_format($stats['chauffeurs'], 0, ',', ' ') }}</h2>
            <p class="muted">En ligne : {{ $stats['chauffeurs_online'] }} · Hors ligne : {{ $stats['chauffeurs_offline'] }}</p>
        </article>
        <article class="card">
            <small>Clients</small>
            <h2>{{ number_format($stats['clients'], 0, ',', ' ') }}</h2>
        </article>
        <article class="card">
            <small>Courses totales</small>
            <h2>{{ number_format($stats['courses'], 0, ',', ' ') }}</h2>
            <p class="muted">Aujourd'hui : {{ number_format($stats['courses_today'], 0, ',', ' ') }}</p>
        </article>
        <article class="card">
            <small>Revenus générés</small>
            <h2>{{ number_format($stats['revenue'], 0, ',', ' ') }} FCFA</h2>
        </article>
        <article class="card">
            <small>Notifications</small>
            <h2>{{ number_format($stats['notifications'], 0, ',', ' ') }}</h2>
        </article>
    </div>

    <section class="actions">
        <form method="POST" action="{{ route('admin.action') }}">
            @csrf
            <input type="hidden" name="action" value="refresh-drivers">
            <button type="submit">Rafraîchir chauffeurs</button>
        </form>

        <form method="POST" action="{{ route('admin.action') }}">
            @csrf
            <input type="hidden" name="action" value="notify-admin">
            <button type="submit">Simuler notification</button>
        </form>
    </section>

    <section class="card">
        <h3>Dernières notifications</h3>
        @if ($recentActivity->isEmpty())
            <p class="muted">Aucune notification récente.</p>
        @else
            <ul>
                @foreach ($recentActivity as $notification)
                    <li>{{ $notification->message ?? 'Notification sans message' }} · {{ $notification->created_at->diffForHumans() }}</li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="card">
        <h3>Sessions actives</h3>
        @if (empty($activeSessions) || $activeSessions->isEmpty())
            <p class="muted">Aucune session active pour le moment.</p>
        @else
            <div class="overflow-auto">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th align="left">Utilisateur</th>
                            <th align="left">Terminal</th>
                            <th align="left">IP</th>
                            <th align="left">Ouverte</th>
                            <th align="left">Expire</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activeSessions as $session)
                            <tr style="border-top:1px solid rgba(255,255,255,0.08);">
                                <td>{{ $session->utilisateur->nom ?? '-' }} {{ $session->utilisateur->prenom ?? '' }}</td>
                                <td>{{ $session->appareil ?? 'inconnu' }}</td>
                                <td>{{ $session->adresseIP ?? '-' }}</td>
                                <td>{{ optional($session->dateCreation)->format('d/m H:i') ?? '-' }}</td>
                                <td>{{ optional($session->dateExpiration)->format('d/m H:i') ?? '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.sessions.terminate', ['session' => $session->id]) }}">
                                        @csrf
                                        <button type="submit" class="text-sm font-semibold" style="background:none; border:none; color:#38bdf8; cursor:pointer; padding:0;">Terminer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="card">
        <h3>Déconnexion</h3>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Quitter</button>
        </form>
    </section>
@endsection
