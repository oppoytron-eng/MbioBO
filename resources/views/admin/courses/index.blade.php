@extends('admin.layout')

@section('title', 'Gestion des courses')

@section('content')
    <section class="grid">
        <article class="card">
            <small>Total des courses</small>
            <h2>{{ number_format($counts['total']) }}</h2>
        </article>
        <article class="card">
            <small>Actives</small>
            <h2>{{ number_format($counts['active']) }}</h2>
        </article>
        <article class="card">
            <small>Annulées</small>
            <h2>{{ number_format($counts['cancelled']) }}</h2>
        </article>
        <article class="card">
            <small>Terminées</small>
            <h2>{{ number_format($counts['completed']) }}</h2>
        </article>
    </section>

    <form class="filters" method="GET" action="{{ route('admin.courses.index') }}">
        <input name="search" type="search" placeholder="Client ou chauffeur" value="{{ $search ?? '' }}" style="flex:1;">
        <select name="filter">
            <option value="">Tout</option>
            <option value="active" {{ ($filter ?? '') === 'active' ? 'selected' : '' }}>Actives</option>
            <option value="cancelled" {{ ($filter ?? '') === 'cancelled' ? 'selected' : '' }}>Annulées</option>
            <option value="completed" {{ ($filter ?? '') === 'completed' ? 'selected' : '' }}>Terminées</option>
        </select>
        <button type="submit">Filtrer</button>
    </form>

    <section class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Course</th>
                    <th>Client</th>
                    <th>Chauffeur</th>
                    <th>Statut</th>
                    <th>Distance / Prix</th>
                    <th>Demandée</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td>#{{ $course->id }}</td>
                        <td>
                            <a href="{{ route('admin.clients.show', $course->client_id) }}">{{ $course->client->utilisateur->prenom ?? '—' }} {{ $course->client->utilisateur->nom ?? '' }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.chauffeurs.show', $course->chauffeur_id) ?? '#' }}">
                                {{ optional($course->chauffeur->utilisateur)->prenom ?? '—' }}
                                {{ optional($course->chauffeur->utilisateur)->nom ?? '' }}
                            </a>
                        </td>
                        <td>
                            @if ($course->est_annule)
                                <span class="badge warn">Annulée</span>
                            @elseif ($course->est_terminee)
                                <span class="badge success">Terminée</span>
                            @else
                                <span class="badge success">{{ $course->statut }}</span>
                            @endif
                        </td>
                        <td>
                            {{ number_format($course->distance_km, 1) }} km<br>
                            {{ number_format($course->prix_final, 0, ',', ' ') }} FCFA
                        </td>
                        <td>{{ optional($course->demande_le)->format('d/m H:i') ?? '—' }}</td>
                        <td>
                            <div class="quick-actions">
                                <a href="{{ route('admin.courses.show', $course->id) }}" class="badge success">Détails</a>
                                @if (! $course->est_annule)
                                    <form method="POST" action="{{ route('admin.courses.cancel', $course->id) }}">
                                        @csrf
                                        <button type="submit" class="badge warn">Annuler</button>
                                    </form>
                                @endif
                                @if (! $course->est_terminee)
                                    <form method="POST" action="{{ route('admin.courses.complete', $course->id) }}">
                                        @csrf
                                        <button type="submit" class="badge success">Terminer</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:2rem 0;">Aucune course trouvée.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            @if($courses->onFirstPage())
                <span>Précédent</span>
            @else
                <a href="{{ $courses->previousPageUrl() }}">Précédent</a>
            @endif
            @if($courses->hasMorePages())
                <a href="{{ $courses->nextPageUrl() }}">Suivant</a>
            @else
                <span>Suivant</span>
            @endif
        </div>
    </section>
@endsection
