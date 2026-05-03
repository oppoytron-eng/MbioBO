@extends('admin.layout')

@section('title', 'Gestion des clients')

@section('content')
    <section class="grid">
        <article class="card">
            <small>Total des clients</small>
            <h2>{{ number_format($stats['total']) }}</h2>
        </article>
        <article class="card">
            <small>Clients désactivés</small>
            <h2>{{ number_format($stats['disabled']) }}</h2>
        </article>
        <article class="card">
            <small>Clients archivés</small>
            <h2>{{ number_format($stats['archived']) }}</h2>
        </article>
    </section>

    <form class="filters" method="GET" action="{{ route('admin.clients.index') }}">
        <input
            name="search"
            placeholder="Nom, email ou téléphone"
            type="search"
            value="{{ $search ?? '' }}"
            autofocus
            style="flex:1;"
        >
        <label>
            <input type="checkbox" name="archived" value="1" {{ $showArchived ? 'checked' : '' }}>
            Afficher les archivés
        </label>
        <button type="submit">Filtrer</button>
    </form>

    <section class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Client</th>
                    <th>Coordonnées</th>
                    <th>Statut</th>
                    <th>Courses</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($clients as $client)
                    @php
                        $courseCount = $client->courses_count ?? 0;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $client->name }}</strong>
                            <div class="muted" style="font-size:0.8rem; margin-top:0.25rem;">
                                Inscrit le {{ optional($client->created_at)->format('d/m/Y') ?: '—' }}
                            </div>
                        </td>
                        <td>
                            {{ $client->email ?? '—' }}<br>
                            <span class="muted">{{ $client->telephone ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $client->est_actif ? 'success' : 'warn' }}">
                                {{ $client->est_actif ? 'Actif' : 'Désactivé' }}
                            </span>
                            @if (! $client->est_actif)
                                <span class="pill">Archivé</span>
                            @endif
                        </td>
                        <td>
                            {{ $courseCount }} course{{ $courseCount > 1 ? 's' : '' }}<br>
                            <small>Note moyenne N/A</small>
                        </td>
                        <td>
                            <div class="quick-actions">
                                <a href="{{ route('admin.clients.show', $client->id) }}" class="badge success">Profil</a>
                                <form method="POST" action="{{ route('admin.clients.toggle-active', $client->id) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="{{ $client->est_actif ? 'disable' : 'enable' }}">
                                    <button type="submit" class="badge {{ $client->est_actif ? 'warn' : 'success' }}">
                                        {{ $client->est_actif ? 'Désactiver' : 'Réactiver' }}
                                    </button>
                                </form>
                                @if ($client->est_actif)
                                    <form method="POST" action="{{ route('admin.clients.archive', $client->id) }}">
                                        @csrf
                                        <button type="submit" class="badge warn">Archiver</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.clients.restore', $client->id) }}">
                                        @csrf
                                        <button type="submit" class="badge success">Restaurer</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:2rem 0;">
                            Aucun client trouvé.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            @if ($clients->onFirstPage())
                <span>Précédent</span>
            @else
                <a href="{{ $clients->previousPageUrl() }}">Précédent</a>
            @endif
            @if ($clients->hasMorePages())
                <a href="{{ $clients->nextPageUrl() }}">Suivant</a>
            @else
                <span>Suivant</span>
            @endif
        </div>
    </section>
@endsection
