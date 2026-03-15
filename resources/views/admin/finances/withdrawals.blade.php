@extends('admin.layout')

@section('title', 'Retraits')

@section('content')
    <section class="card">
        <div class="header-grid">
            <div>
                <h2>Historique des retraits</h2>
                <p class="muted">Validez ou rejetez les demandes soumises par les chauffeurs.</p>
            </div>
        </div>

        @if(session('status'))
            <div class="status">
                {{ session('status') }}
            </div>
        @endif

        @if($withdrawals->isEmpty())
            <p>Aucune demande.</p>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Chauffeur</th>
                            <th>Montant</th>
                            <th>Demandée le</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawals as $withdrawal)
                            <tr>
                                <td>{{ optional($withdrawal->chauffeur->utilisateur)->prenom ?? '—' }} {{ optional($withdrawal->chauffeur->utilisateur)->nom ?? '' }}</td>
                                <td>{{ number_format($withdrawal->amount, 0, ',', ' ') }} FCFA</td>
                                <td>{{ optional($withdrawal->requested_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge {{ $withdrawal->status === 'approved' ? 'success' : ($withdrawal->status === 'rejected' ? 'warn' : '') }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </td>
                                <td style="display:flex; gap:0.5rem;">
                                    @if($withdrawal->status === 'pending')
                                        <form method="POST" action="{{ route('admin.finances.withdrawals.approve', $withdrawal) }}">
                                            @csrf
                                            <button class="badge success" style="border:none;">Valider</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.finances.withdrawals.reject', $withdrawal) }}">
                                            @csrf
                                            <button class="badge warn" style="border:none;">Rejeter</button>
                                        </form>
                                    @else
                                        <span class="muted">Traitée le {{ optional($withdrawal->processed_at)->format('d/m/Y H:i') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </section>
@endsection
