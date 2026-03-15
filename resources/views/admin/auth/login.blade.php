<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion admin</title>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: white;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: rgba(15, 23, 42, 0.9);
            padding: 2rem;
            border-radius: 1rem;
            width: min(420px, 100%);
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.6);
        }
        h1 {
            margin-top: 0;
            font-size: 1.8rem;
            letter-spacing: 0.05em;
        }
        label {
            display: block;
            margin-top: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        input {
            width: 100%;
            padding: 0.85rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(15, 23, 42, 0.65);
            color: white;
            margin-top: 0.4rem;
            font-size: 1rem;
        }
        button {
            margin-top: 1.5rem;
            width: 100%;
            background: #38bdf8;
            border: none;
            padding: 0.95rem;
            color: #0f172a;
            font-weight: 700;
            border-radius: 0.75rem;
            cursor: pointer;
        }
        .errors {
            margin-top: 1rem;
            padding: 0.8rem;
            background: rgba(248, 113, 113, 0.2);
            border-radius: 0.5rem;
        }
        .errors p {
            margin: 0;
            color: #fee2e2;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Connexion admin</h1>
    @if ($errors->any())
        <div class="errors">
            <p>{{ $errors->first() }}</p>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" required>

        <div class="remember">
            <input id="remember" type="checkbox" name="remember">
            <label for="remember" style="margin:0; font-size:0.85rem; text-transform:none;">Se souvenir de moi</label>
        </div>

        <button type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>
