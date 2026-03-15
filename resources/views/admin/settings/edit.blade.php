@extends('admin.layout')

@section('title', 'Paramètres de l’application')

@section('content')
    <section class="card">
        <div class="header-grid">
            <div>
                <h2>Paramètres</h2>
                <p class="muted">Ajustez les tarifs de base, activez ou désactivez les notifications système.</p>
            </div>
        </div>

        @if(session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:1rem;">
                <label>
                    Prix de base (FCFA)
                    <input type="number" step="0.01" name="base_fare" value="{{ old('base_fare', $settings['base_fare']->value ?? '') }}" style="width:100%; margin-top:0.3rem; padding:0.4rem;">
                </label>
                <label>
                    Tarif au km (FCFA)
                    <input type="number" step="0.01" name="per_km_rate" value="{{ old('per_km_rate', $settings['per_km_rate']->value ?? '') }}" style="width:100%; margin-top:0.3rem; padding:0.4rem;">
                </label>
                <label>
                    Tarif à la minute (FCFA)
                    <input type="number" step="0.01" name="per_minute_rate" value="{{ old('per_minute_rate', $settings['per_minute_rate']->value ?? '') }}" style="width:100%; margin-top:0.3rem; padding:0.4rem;">
                </label>
            </div>

            <div style="margin-top:1rem;">
                <label>
                    <input type="checkbox" name="system_notifications_enabled" value="1" {{ old('system_notifications_enabled', $settings['system_notifications_enabled']->value ?? '0') === '1' ? 'checked' : '' }}>
                    Activer les notifications système
                </label>
            </div>

            <div style="margin-top:1rem;">
                <label>
                    Modèle de notification système
                    <textarea name="system_notification_template" rows="3" style="width:100%; margin-top:0.3rem; padding:0.4rem;">{{ old('system_notification_template', $settings['system_notification_template']->value ?? '') }}</textarea>
                </label>
                <p class="muted">Utilisez {chauffeur} ou {client} pour interpoler des valeurs dynamiques.</p>
            </div>

            <button type="submit" class="badge success" style="border:none; margin-top:1rem;">Enregistrer les paramètres</button>
        </form>
    </section>
@endsection
