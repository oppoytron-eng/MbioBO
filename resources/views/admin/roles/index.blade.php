@extends('admin.layout')

@section('title', 'Rôles administrateurs')

@section('content')
    <section class="card">
        <div class="header-grid">
            <div>
                <h2>Gestion des rôles admin</h2>
                <p class="muted">Créez des rôles et attribuez-les aux comptes administrateurs.</p>
            </div>
        </div>

        @if(session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div class="filters" style="gap:1rem;">
                <label>
                    Nom du rôle
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="ex. Super Admin" style="margin:0.3rem 0; padding:0.4rem;">
                </label>
                <label>
                    Description
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Ce rôle permet de..." style="margin:0.3rem 0; padding:0.4rem;">
                </label>
                <button type="submit" class="badge success" style="border:none;">Créer le rôle</button>
            </div>
            @error('name')
                <p class="muted">{{ $message }}</p>
            @enderror
        </form>

        <div class="table-wrapper" style="margin-top:1rem;">
            <table>
                <thead>
                    <tr>
                        <th>Rôle</th>
                        <th>Description</th>
                        <th>Admins</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->description ?? '—' }}</td>
                            <td>
                                @foreach($admins as $admin)
                                    <form method="POST" action="{{ route('admin.roles.toggle', ['role' => $role, 'utilisateur' => $admin]) }}" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="badge {{ $admin->hasAdminRole($role->name) ? 'success' : 'warn' }}" style="border:none;">
                                            {{ $admin->hasAdminRole($role->name) ? 'Attribué' : 'Attribuer' }}
                                        </button>
                                        <small>{{ $admin->nom }} {{ $admin->prenom }}</small>
                                    </form>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
