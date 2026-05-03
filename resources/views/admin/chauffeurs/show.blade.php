@extends('admin.layout')

@section('title', 'Profil chauffeur')

@section('content')
    <section class="card">
        <h2>Profil du chauffeur</h2>
        <div style="display:flex; flex-wrap:wrap; gap:1rem;">
            <div>
                <strong>{{ $chauffeur->name }}</strong><br>
                <small class="muted">Permis {{ $chauffeur->chauffeurProfile ? $chauffeur->chauffeurProfile->numero_permis : '—' }}</small>
            </div>
            <div>
                <small>Email</small><br>
                {{ $chauffeur->email ?? '—' }}
            </div>
            <div>
                <small>Téléphone</small><br>
                {{ $chauffeur->telephone ?? '—' }}
            </div>
            <div>
                <small>Statut opérationnel</small><br>
                <span class="badge {{ $chauffeur->est_actif ? 'success' : 'warn' }}">
                    {{ $chauffeur->est_actif ? 'Actif' : 'Inactif' }}
                </span>
            </div>
            <div>
                <small>Statut connexion</small><br>
                <span class="badge {{ ($chauffeur->chauffeurStatus && $chauffeur->chauffeurStatus->statut === 'En ligne') || ($chauffeur->chauffeurProfile && $chauffeur->chauffeurProfile->statut === 'En ligne') ? 'success' : 'warn' }}">
                    @if($chauffeur->chauffeurStatus)
                        {{ $chauffeur->chauffeurStatus->statut }}
                    @elseif($chauffeur->chauffeurProfile)
                        {{ $chauffeur->chauffeurProfile->statut }}
                    @else
                        Hors ligne
                    @endif
                </span>
            </div>
        </div>

        <div class="quick-actions" style="margin-top:1rem;">
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

        <div style="margin-top:1rem; display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
            <div class="card">
                <small>Total affrétés</small>
                <h3>{{ $courseStats['total'] }}</h3>
            </div>
            <div class="card">
                <small>Courses complétées</small>
                <h3>{{ $courseStats['completed'] }}</h3>
            </div>
            <div class="card">
                <small>Revenu total</small>
                <h3>{{ number_format($courseStats['revenue'] ?? 0, 0, ',', ' ') }} FCFA</h3>
            </div>
            <div class="card">
                <small>Distance moyenne</small>
                <h3>{{ number_format($courseStats['average_distance'] ?? 0, 1) }} km</h3>
            </div>
        </div>
    </section>

    <section class="card" style="margin-top:1rem;">
        <h3>Documents</h3>
        @if (!$chauffeur->driverDocuments || $chauffeur->driverDocuments->isEmpty())
            <p>Aucun document à valider.</p>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($chauffeur->driverDocuments as $document)
                            <tr>
                                <td>{{ ucfirst($document->type) }}</td>
                                <td>
                                    <span class="badge {{ $document->status === 'approved' ? 'success' : ($document->status === 'rejected' ? 'warn' : '') }}">
                                        {{ $document->status === 'approved' ? 'Validé' : ($document->status === 'rejected' ? 'Rejeté' : 'En attente') }}
                                    </span>
                                    <div class="muted" style="font-size:0.8rem;">
                                        mis à jour le {{ optional($document->reviewed_at)->format('d/m/Y H:i') ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="quick-actions">
                                        <form method="POST" action="{{ route('admin.chauffeurs.documents.review', ['chauffeur' => $chauffeur->id, 'document' => $document->id]) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="badge success">Valider</button>
                                        </form>
                                        <form method="POST" class="quick-review" action="{{ route('admin.chauffeurs.documents.review', ['chauffeur' => $chauffeur->id, 'document' => $document->id]) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="text" name="notes" placeholder="Raison (optionnelle)" style="font-size:0.8rem; border-radius:0.5rem; padding:0.3rem; border:1px solid rgba(255,255,255,0.2); background:transparent; color:white;">
                                            <button type="submit" class="badge warn">Rejeter</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="card" style="margin-top:1rem;">
        <h3>Historique des courses</h3>
        @if ($history->isEmpty())
            <p>Aucune course enregistrée.</p>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Statut</th>
                            <th>Trajet</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $course)
                            <tr>
                                <td>{{ optional($course->termine_le)->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    {{ optional($course->client?->utilisateur)->prenom ?? '—' }}
                                    {{ optional($course->client?->utilisateur)->nom ?? '' }}
                                </td>
                                <td>
                                    <span class="badge success">{{ $course->statut }}</span>
                                </td>
                                <td>
                                    <small>{{ $course->adresse_depart }}</small><br>
                                    <strong>{{ $course->adresse_arrivee }}</strong>
                                </td>
                                <td>{{ number_format($course->prix_final ?? 0, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
