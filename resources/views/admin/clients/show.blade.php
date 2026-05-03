@extends('admin.layout')

@section('title', 'Profil client')

@section('content')
    <section class="card">
        <h2>Profil du client</h2>
        <p style="margin-bottom:0.5rem;">
            <strong>{{ $client->name }}</strong>
            <span class="pill">{{ $client->role ?? 'Client' }}</span>
        </p>
        <div style="display:flex; flex-wrap:wrap; gap:1rem;">
            <div>
                <small>Email</small><br>
                {{ $client->email ?? '—' }}
            </div>
            <div>
                <small>Téléphone</small><br>
                {{ $client->telephone ?? '—' }}
            </div>
            <div>
                <small>Statut actuel</small><br>
                <span class="badge {{ $client->est_actif ? 'success' : 'warn' }}">
                    {{ $client->est_actif ? 'Actif' : 'Suspendu' }}
                </span>
            </div>
            <div>
                <small>Inscrit le</small><br>
                {{ optional($client->created_at)->format('d/m/Y H:i') ?: '—' }}
            </div>
            <div>
                <small>Dernière archive</small><br>
                {{ $client->est_actif ? 'Jamais' : 'Archivé' }}
            </div>
        </div>

        <div class="quick-actions" style="margin-top:1rem;">
            @if ($client->est_actif)
                <form method="POST" action="{{ route('admin.clients.toggle-active', $client->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="{{ $client->est_actif ? 'disable' : 'enable' }}">
                    <button type="submit" class="badge {{ $client->est_actif ? 'warn' : 'success' }}">
                        {{ $client->est_actif ? 'Désactiver le client' : 'Réactiver le client' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.clients.archive', $client->id) }}">
                    @csrf
                    <button type="submit" class="badge warn">Archiver</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.clients.restore', $client->id) }}">
                    @csrf
                    <button type="submit" class="badge success">Restaurer</button>
                </form>
                <form method="POST" action="{{ route('admin.clients.archive', $client->id) }}">
                    @csrf
                    <input type="hidden" name="force" value="1">
                    <button type="submit" class="badge warn">Supprimer définitivement</button>
                </form>
            @endif
        </div>

        <div style="margin-top:1rem; display:flex; gap:1rem; flex-wrap:wrap;">
            <div>
                <small>Note moyenne</small><br>
                {{ number_format($client->note_moyenne, 1) }}
            </div>
            <div>
                <small>Nombre de courses</small><br>
                {{ $client->courses_count ?? 0 }}
            </div>
        </div>
    </section>

    <section class="card" style="margin-top:1rem;">
        <h3>Historique des courses</h3>
        @if ($history->isEmpty())
            <p>Aucune course enregistrée pour ce client.</p>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Chauffeur</th>
                        <th>Statut</th>
                        <th>Trajet</th>
                        <th>Montant</th>
                        <th>Mode de paiement</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($history as $course)
                        @php
                            $chauffeurProfil = optional(optional($course->chauffeur)->utilisateur);
                            $statusColor = in_array($course->statut, ['En course', 'En ligne']) ? 'success' : 'warn';
                        @endphp
                        <tr>
                            <td>{{ optional($course->termine_le)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                {{ $chauffeurProfil->prenom ?? '—' }}
                                {{ $chauffeurProfil->nom ?? '' }}
                            </td>
                            <td>
                                <span class="badge {{ $statusColor }}">{{ $course->statut }}</span>
                            </td>
                            <td>
                                <small>{{ $course->adresse_depart }}</small><br>
                                <strong>{{ $course->adresse_arrivee }}</strong>
                            </td>
                            <td>
                                {{ $course->prix_final ? number_format($course->prix_final, 0, ',', ' ') . ' FCFA' : '—' }}
                            </td>
                            <td>{{ $course->modePaiement ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
