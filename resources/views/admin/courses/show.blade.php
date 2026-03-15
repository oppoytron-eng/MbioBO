@extends('admin.layout')

@section('title', 'Détails de la course')

@section('content')
    <section class="card">
        <h2>Course #{{ $course->id }}</h2>
        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
            <span class="pill">
                @if($course->est_annule)
                    Annulée
                @elseif($course->est_terminee)
                    Terminée
                @else
                    {{ $course->statut }}
                @endif
            </span>
            <span class="pill">
                {{ number_format($course->distance_km, 1) }} km · {{ number_format($course->prix_final, 0, ',', ' ') }} FCFA
            </span>
            <span class="pill">Demandée le {{ optional($course->demande_le)->format('d/m/Y H:i') ?? '—' }}</span>
            <span class="pill">Terminée le {{ optional($course->termine_le)->format('d/m/Y H:i') ?? '—' }}</span>
        </div>

        <div class="quick-actions" style="margin-bottom:1rem;">
            <a class="badge success" href="{{ route('admin.clients.show', $course->client_id) }}">Voir le client</a>
            <a class="badge success" href="{{ route('admin.chauffeurs.show', $course->chauffeur_id) ?? '#' }}">Voir le chauffeur</a>
            @if(! $course->est_annule)
                <form method="POST" action="{{ route('admin.courses.cancel', $course->id) }}">
                    @csrf
                    <button type="submit" class="badge warn">Annuler</button>
                </form>
            @endif
            @if(! $course->est_terminee)
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
                <div>{{ $course->adresse_depart }} ({{ $course->lat_depart }}, {{ $course->lng_depart }})</div>
            </div>
            <div>
                <strong>Arrivée</strong>
                <div>{{ $course->adresse_arrivee }} ({{ $course->lat_arrivee }}, {{ $course->lng_arrivee }})</div>
            </div>
            <div>
                <strong>Mode de paiement</strong>
                <div>{{ $course->modePaiement }}</div>
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
                @if($course->est_terminee)
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
                            <td>{{ optional($row->termine_le)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ optional($row->chauffeur->utilisateur)->prenom ?? '—' }} {{ optional($row->chauffeur->utilisateur)->nom ?? '' }}</td>
                            <td>
                                @if($row->est_annule)
                                    <span class="badge warn">Annulée</span>
                                @elseif($row->est_terminee)
                                    <span class="badge success">Terminée</span>
                                @else
                                    <span class="badge success">{{ $row->statut }}</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $row->adresse_depart }}</small><br>
                                <strong>{{ $row->adresse_arrivee }}</strong>
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
        $positionRoute = $course->positions->map(function ($position) {
            return [$position->latitude, $position->longitude];
        })->values();
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapEl = document.getElementById('course-map');
            if (!mapEl) {
                return;
            }

            const startLat = Number.parseFloat(@json($course->lat_depart ?? null));
            const startLng = Number.parseFloat(@json($course->lng_depart ?? null));
            const endLat = Number.parseFloat(@json($course->lat_arrivee ?? null));
            const endLng = Number.parseFloat(@json($course->lng_arrivee ?? null));

            const hasCoordinates = [startLat, startLng, endLat, endLng].every(Number.isFinite);

            if (!hasCoordinates) {
                mapEl.innerHTML = "<p class=\"muted\" style=\"padding:1rem;\">Coordonnées GPS manquantes pour afficher la carte.</p>";
                return;
            }

            const positions = @json($positionRoute);
            const pathCoords = positions.length ? positions : [[startLat, startLng], [endLat, endLng]];
            const map = L.map(mapEl, {
                zoomControl: false,
                scrollWheelZoom: false,
            })
                .setView(pathCoords[0], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            const startMarker = L.marker([startLat, startLng]).addTo(map);
            const endMarker = L.marker([endLat, endLng]).addTo(map);

            startMarker.bindPopup("Départ");
            endMarker.bindPopup("Arrivée");

            L.control.scale({imperial: false}).addTo(map);

            let routeLayer = L.polyline(pathCoords, {
                color: '#38bdf8',
                weight: 4,
                opacity: 0.8,
                lineCap: 'round',
            }).addTo(map);

            const bounds = routeLayer.getBounds().isValid()
                ? routeLayer.getBounds()
                : L.latLngBounds([pathCoords[0], [pathCoords[0][0] + 0.001, pathCoords[0][1] + 0.001]]);
            map.fitBounds(bounds, {padding: [40, 40]});

            if (!positions.length) {
                const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;
                fetch(osrmUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.routes?.length) {
                            return;
                        }
                        const roadCoords = data.routes[0].geometry.coordinates.map(([lng, lat]) => [lat, lng]);
                        if (!roadCoords.length) {
                            return;
                        }
                        routeLayer.setLatLngs(roadCoords);
                        map.fitBounds(L.latLngBounds(roadCoords), {padding: [40, 40]});
                    })
                    .catch(() => {
                        /* ignore OSRM failures */
                    });
            }

            setTimeout(function () {
                map.invalidateSize();
            }, 200);
        });
    </script>
@endsection
