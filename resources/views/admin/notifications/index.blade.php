@extends('admin.layout')

@section('title', 'Notifications push')

@section('content')
    <section class="card">
        <div class="header-grid">
            <div>
                <h2>Envoyer une notification</h2>
                <p class="muted">Choisissez une cible et un message. Les notifications sont sauvegardées pour l’historique.</p>
            </div>
        </div>

        @if(session('status'))
            <div class="status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.notifications.store') }}">
            @csrf

            <div class="filters" style="gap:1rem;">
                <label>
                    <input type="radio" name="target" value="global" {{ old('target', 'global') === 'global' ? 'checked' : '' }}>
                    Notification globale
                </label>
                <label>
                    <input type="radio" name="target" value="chauffeurs" {{ old('target') === 'chauffeurs' ? 'checked' : '' }}>
                    Chauffeurs
                </label>
                <label>
                    <input type="radio" name="target" value="clients" {{ old('target') === 'clients' ? 'checked' : '' }}>
                    Clients
                </label>
            </div>

            <div style="margin-bottom:1rem;">
                <label for="title">Titre (optionnel)</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="Résumé de la notification" style="width:100%; margin-top:0.5rem; padding:0.6rem;">
                @error('title')
                    <div class="muted">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:1rem;">
                <label for="message">Message</label>
                <textarea name="message" id="message" rows="3" placeholder="Entrez le texte à envoyer" style="width:100%; margin-top:0.5rem; padding:0.6rem;">{{ old('message') }}</textarea>
                @error('message')
                    <div class="muted">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="badge success" style="border:none; cursor:pointer;">Envoyer la notification</button>
        </form>
    </section>

    <section class="card" style="margin-top:1rem;">
        <div class="header-grid">
            <div>
                <h2>Historique</h2>
                <p class="muted">Les notifications les plus récentes apparaissent en haut. Vous pouvez supprimer un envoi.</p>
            </div>
        </div>

        @if($notifications->isEmpty())
            <p>Aucune notification envoyée pour le moment.</p>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Cible</th>
                            <th>Titre</th>
                            <th>Message</th>
                            <th>Envoyée le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $notification->target)) }}</td>
                                <td>{{ $notification->title ?? '—' }}</td>
                                <td style="max-width:320px;">{{ $notification->message }}</td>
                                <td>{{ optional($notification->sent_at)->format('d/m/Y H:i') ?? $notification->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="badge warn" style="border:none;">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
@endsection
