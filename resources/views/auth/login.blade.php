<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | {{ config('vitrine.nom') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta name="theme-color" content="#100d18">
    <script>
        (function () {
            try {
                if (localStorage.getItem('theme') !== 'light') document.documentElement.classList.add('dark');
            } catch (e) { document.documentElement.classList.add('dark'); }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-hidden bg-app px-4 text-slate-700 antialiased">

@php $livreur = request('profil') === 'livreur'; @endphp

<div class="pointer-events-none absolute inset-x-0 top-0 hidden h-96 dark:block"
     style="background: radial-gradient(60% 100% at 50% 0%, rgba(139, 92, 246, .32), transparent 70%)"></div>

<a href="{{ route('accueil') }}"
   class="absolute left-4 top-4 flex items-center gap-2 rounded-full px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 sm:left-6 sm:top-6">
    <span aria-hidden="true">←</span> Retour à l'accueil
</a>

<div class="relative w-full max-w-sm">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white">
            <x-icon name="{{ $livreur ? 'truck' : 'cube' }}" class="h-6 w-6" />
        </div>
        <h1 class="text-3xl font-medium tracking-tight">
            <span class="bg-linear-to-r from-blue-500 via-violet-500 to-fuchsia-400 bg-clip-text text-transparent">Bienvenue</span>
        </h1>
        <p class="mt-1 text-slate-500">
            {{ $livreur ? 'Connectez-vous à votre espace livreur' : 'Connectez-vous à votre espace' }}
        </p>
    </div>

    <form action="{{ route('login.attempt') }}" method="POST"
          class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">E-mail</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-xl border px-4 py-2.5 text-sm focus:outline-none
                          {{ $errors->has('email') ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Mot de passe</label>
            <input type="password" id="password" name="password" required
                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1"> Se souvenir de moi
        </label>

        <button class="w-full rounded-full bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
            Se connecter
        </button>
    </form>

    <div class="mt-6 space-y-2 text-center text-sm text-slate-500">
        @if ($livreur)
            <p>Votre compte livreur est créé par l'administration. Un problème de connexion ?
                <a href="mailto:{{ config('vitrine.email') }}" class="text-blue-600 hover:underline">Contactez-nous</a>.</p>
            <p>Vous êtes client ? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Connectez-vous ici</a></p>
        @else
            <p>Pas encore de compte ? <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:underline">S'inscrire</a></p>
            <p>Vous êtes livreur ? <a href="{{ route('login', ['profil' => 'livreur']) }}" class="text-blue-600 hover:underline">Connectez-vous ici</a></p>
        @endif
    </div>
</div>

</body>
</html>