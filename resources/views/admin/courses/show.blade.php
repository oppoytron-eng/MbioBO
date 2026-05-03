@extends('admin.layout')

@section('title', 'Détails de la course')

@section('content')
    <section class="card">
        <h2>Course #{{ $course->id }}</h2>
        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
            <span class="pill">
                @if($course->statut === 'annulee')
                    Annulée
                @elseif($course->statut === 'terminee')
                    Terminée
                @elseif($course->statut === 'en_cours')
                    En cours
                @elseif($course->statut === 'acceptee')
                    Acceptée
                @else
                    {{ $course->statut }}
                @endif
            </span>
            <span class="pill">
                {{ number_format($course->distance_meters / 1000, 1) }} km · {{ number_format($course->prix_final, 0, ',', ' ') }} FCFA
            </span>
            <span class="pill">Demandée le {{ optional($course->created_at)->format('d/m/Y H:i') ?? '—' }}</span>
            <span class="pill">Terminée le {{ optional($course->updated_at)->format('d/m/Y H:i') ?? '—' }}</span>
        </div>

        <div class="quick-actions" style="margin-bottom:1rem;">
            <a class="badge success" href="{{ $course->client ? route('admin.clients.show', $course->client_id) : '#' }}">Voir le client</a>
            <a class="badge success" href="{{ $course->chauffeur ? route('admin.chauffeurs.show', $course->chauffeur_id) : '#' }}">Voir le chauffeur</a>
            @if($course->statut !== 'annulee')
                <form method="POST" action="{{ route('admin.courses.cancel', $course->id) }}">
                    @csrf
                    <button type="submit" class="badge warn">Annuler</button>
                </form>
            @endif
            @if($course->statut !== 'terminee')
                <form method="POST" action="{{ route('admin.courses.complete', $course->id) }}">
                    @csrf
                    <button type="submit" class="badge success">Terminer</button>
                </form>
            @endif
        </div>

        <div id="course-map" style="height:320px; border-radius:1rem; overflow:hidden; margin-bottom:1rem;"></div>
        <div style="display:flex; flex-wrap:wrap; gap:1.5rem;">
            <div>
                <strong>Départ</strong>
                <div>{{ $course->depart_latitude ?? '—' }}, {{ $course->depart_longitude ?? '—' }}</div>
            </div>
            <div>
                <strong>Arrivée</strong>
                <div>{{ $course->arrivee_latitude ?? '—' }}, {{ $course->arrivee_longitude ?? '—' }}</div>
            </div>
            <div>
                <strong>Mode de paiement</strong>
                <div>{{ $course->mode_paiement ?? '—' }}</div>
            </div>
        </div>
    </section>

    <section class="card" style="margin-top:1rem;">
        <h3>Paiements</h3>
        <div style="display:flex; flex-wrap:wrap; gap:1.5rem;">
            <div>
                <strong>Prix estimé</strong>
                <div>{{ number_format($course->prix_estime, 0, ',', ' ') }} FCFA</div>
            </div>
            <div>
                <strong>Prix final</strong>
                <div>{{ number_format($course->prix_final, 0, ',', ' ') }} FCFA</div>
            </div>
            <div>
                <strong>Statut du paiement</strong>
                @if($course->statut === 'terminee')
                    <div class="badge success">Payé</div>
                @else
                    <div class="badge warn">En attente</div>
                @endif
            </div>
        </div>
    </section>

    <section class="card" style="margin-top:1rem;">
        <h3>Historique client (6 dernières courses)</h3>
        @if($history->isEmpty())
            <p>Aucune autre course enregistrée pour ce client.</p>
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
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($history as $row)
                        <tr>
                            <td>{{ optional($row->updated_at)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ $row->chauffeur->name ?? '—' }}</td>
                            <td>
                                @if($row->statut === 'annulee')
                                    <span class="badge warn">Annulée</span>
                                @elseif($row->statut === 'terminee')
                                    <span class="badge success">Terminée</span>
                                @else
                                    <span class="badge secondary">{{ $row->statut }}</span>
                                @endif
                            </td>
                            <td>
                                <small>Départ: {{ $row->depart_latitude ?? '—' }}, {{ $row->depart_longitude ?? '—' }}</small><br>
                                <strong>Arrivée: {{ $row->arrivee_latitude ?? '—' }}, {{ $row->arrivee_longitude ?? '—' }}</strong>
                            </td>
                            <td>{{ number_format($row->prix_final, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection

@section('scripts')
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
              crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                crossorigin=""></script>
    @endonce

    @php
        $positionRoute = $course->tracks->map(function ($track) {
            return [$track->latitude, $track->longitude];
        })->values();
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startLat = Number.parseFloat({{ $course->depart_latitude ?? 'null' }});
            const startLng = Number.parseFloat({{ $course->depart_longitude ?? 'null' }});
            const endLat = Number.parseFloat({{ $course->arrivee_latitude ?? 'null' }});
            const endLng = Number.parseFloat({{ $course->arrivee_longitude ?? 'null' }});

            const hasCoordinates = [startLat, startLng, endLat, endLng].every(Number.isFinite);

            if (hasCoordinates) {
                const map = L.map('course-map').setView([startLat, startLng], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: ' OpenStreetMap contributors'
                }).addTo(map);

                const startIcon = L.divIcon({
                    html: '',
                    iconSize: [20, 20],
                    className: 'custom-div-icon'
                });

                const endIcon = L.divIcon({
                    html: '',
                    iconSize: [20, 20],
                    className: 'custom-div-icon'
                });

                L.marker([startLat, startLng], {icon: startIcon}).addTo(map)
                    .bindPopup('Départ');

                L.marker([endLat, endLng], {icon: endIcon}).addTo(map)
                    .bindPopup('Arrivée');

                if (@json($positionRoute->count()) > 0) {
                    const routeCoords = @json($positionRoute);
                    L.polyline(routeCoords, {color: 'blue', weight: 4, opacity: 0.7}).addTo(map);
                } else {
                    L.polyline([[startLat, startLng], [endLat, endLng]], {color: 'blue', weight: 4, opacity: 0.7}).addTo(map);
                }

                const group = L.featureGroup([startLat, startLng], [endLat, endLng]);
                map.fitBounds(group.getBounds().pad(0.1));
            }

            setTimeout(function () {
                map.invalidateSize();
            }, 200);
        });
    </script>
@endsection
