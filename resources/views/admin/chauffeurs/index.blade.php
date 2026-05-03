@extends('admin.layout')

@section('title', 'Gestion des chauffeurs')

@section('content')
    <section class="grid">
        <article class="card">
            <small>Total des chauffeurs</small>
            <h2>{{ number_format($stats['total']) }}</h2>
        </article>
        <article class="card">
            <small>Chauffeurs actifs</small>
            <h2>{{ number_format($stats['active']) }}</h2>
        </article>
        <article class="card">
            <small>Chauffeurs suspendus</small>
            <h2>{{ number_format($stats['suspended']) }}</h2>
        </article>
        <article class="card">
            <small>Chauffeurs archivés</small>
            <h2>{{ number_format($stats['archived']) }}</h2>
        </article>
    </section>

    <form class="filters" method="GET" action="{{ route('admin.chauffeurs.index') }}">
        <input
            name="search"
            placeholder="Nom, email, téléphone"
            type="search"
            value="{{ $search ?? '' }}"
            style="flex:1;"
        >
        <select name="status">
            <option value="">Tous les statuts</option>
            <option value="actif" {{ $statusFilter === 'actif' ? 'selected' : '' }}>Actif</option>
            <option value="inactif" {{ $statusFilter === 'inactif' ? 'selected' : '' }}>Inactif</option>
            <option value="suspendu" {{ $statusFilter === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
        </select>
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
                        <th>Chauffeur</th>
                        <th>Contact</th>
                        <th>Statut &amp; documents</th>
                        <th>Courses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($chauffeurs as $chauffeur)
                    <tr>
                        <td>
                            <strong>{{ $chauffeur->name }}</strong><br>
                            <small class="muted">Permis {{ $chauffeur->chauffeurProfile ? $chauffeur->chauffeurProfile->numero_permis : '—' }}</small>
                        </td>
                        <td>
                            {{ $chauffeur->email ?? '—' }}<br>
                            <span class="muted">{{ $chauffeur->telephone ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $chauffeur->est_actif ? 'success' : 'warn' }}">
                                {{ $chauffeur->est_actif ? 'Actif' : 'Inactif' }}
                            </span>
                            <div class="pill" style="margin-left:0;">
                                @if($chauffeur->chauffeurStatus)
                                    {{ $chauffeur->chauffeurStatus->statut_operationnel === 'actif' ? 'Actif' : ($chauffeur->chauffeurStatus->statut_operationnel === 'suspendu' ? 'Suspendu' : 'Inactif') }}
                                @elseif($chauffeur->chauffeurProfile)
                                    {{ $chauffeur->chauffeurProfile->statut_operationnel === 'actif' ? 'Actif' : ($chauffeur->chauffeurProfile->statut_operationnel === 'suspendu' ? 'Suspendu' : 'Inactif') }}
                                @else
                                    Actif
                                @endif
                            </div>
                        </td>
                        <td>
                            {{ $chauffeur->courses_count ?? 0 }} course{{ ($chauffeur->courses_count ?? 0) > 1 ? 's' : '' }}<br>
                            <small>Note moyenne N/A</small>
                        </td>
                        <td>
                            <div class="quick-actions">
                                <a href="{{ route('admin.chauffeurs.show', $chauffeur->id) }}" class="badge success">Profil</a>

                                <form method="POST" action="{{ route('admin.chauffeurs.status', $chauffeur->id) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="badge success">Activer</button>
                                </form>
                                <form method="POST" action="{{ route('admin.chauffeurs.status', $chauffeur->id) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="badge warn">Désactiver</button>
                                </form>
                                <form method="POST" action="{{ route('admin.chauffeurs.status', $chauffeur->id) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="suspend">
                                    <button type="submit" class="badge warn">Suspendre</button>
                                </form>

                                @if (! $chauffeur->est_actif)
                                    <form method="POST" action="{{ route('admin.chauffeurs.restore', $chauffeur->id) }}">
                                        @csrf
                                        <button type="submit" class="badge success">Restaurer</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.chauffeurs.archive', $chauffeur->id) }}">
                                        @csrf
                                        <button type="submit" class="badge warn">Archiver</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:2rem 0;">
                            Aucun chauffeur trouvé.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            @if ($chauffeurs->onFirstPage())
                <span>Précédent</span>
            @else
                <a href="{{ $chauffeurs->previousPageUrl() }}">Précédent</a>
            @endif
            @if ($chauffeurs->hasMorePages())
                <a href="{{ $chauffeurs->nextPageUrl() }}">Suivant</a>
            @else
                <span>Suivant</span>
            @endif
        </div>
    </section>
@endsection
