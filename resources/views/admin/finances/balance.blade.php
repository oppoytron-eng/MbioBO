@extends('admin.layout')

@section('title', 'Solde chauffeur')

@section('content')
    <section class="card">
        <div class="header-grid">
            <div>
                <h2>Solde de {{ optional($chauffeur->utilisateur)->prenom ?? '' }} {{ optional($chauffeur->utilisateur)->nom ?? '' }}</h2>
                <p class="muted">Somme des courses terminées moins les retraits déjà validés.</p>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Total versé</h3>
                <p>{{ number_format($completed, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="card">
                <h3>Retraits approuvés</h3>
                <p>{{ number_format($withdrawn, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="card">
                <h3>Solde disponible</h3>
                <p>{{ number_format(max($completed - $withdrawn, 0), 0, ',', ' ') }} FCFA</p>
            </div>
        </div>

        <section class="card" style="margin-top:1rem;">
            <h3>Retraits en attente</h3>
            @if($pending->isEmpty())
                <p>Aucune demande en attente.</p>
            @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Montant</th>
                                <th>Demandée le</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $withdrawal)
                                <tr>
                                    <td>{{ number_format($withdrawal->amount, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ optional($withdrawal->requested_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $withdrawal->note ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </section>
@endsection
