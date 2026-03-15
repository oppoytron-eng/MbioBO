@extends('admin.layout')

@section('title', 'Finances · Transactions')

@section('content')
    <section class="card">
        <div class="header-grid">
            <div>
                <h2>Transactions</h2>
                <p class="muted">Liste des courses terminées et des paiements enregistrés.</p>
            </div>
        </div>

        @if($courses->isEmpty())
            <p>Aucune transaction trouvée.</p>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Client</th>
                            <th>Chauffeur</th>
                            <th>Montant FCFA</th>
                            <th>Mode de paiement</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                            <tr>
                                <td>{{ $course->id }}</td>
                                <td>{{ optional($course->client->utilisateur)->prenom ?? '—' }} {{ optional($course->client->utilisateur)->nom ?? '' }}</td>
                                <td>{{ optional($course->chauffeur->utilisateur)->prenom ?? '—' }} {{ optional($course->chauffeur->utilisateur)->nom ?? '' }}</td>
                                <td>{{ number_format($course->prix_final, 0, ',', ' ') }}</td>
                                <td>{{ $course->modePaiement }}</td>
                                <td>
                                    @if($course->est_annule)
                                        <span class="badge warn">Annulée</span>
                                    @elseif($course->est_terminee)
                                        <span class="badge success">Terminée</span>
                                    @else
                                        <span class="badge">En cours</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.courses.show', $course) }}" class="badge success">Voir les paiements</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                {{ $courses->links() }}
            </div>
        @endif
    </section>
@endsection
