<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | {{ config('vitrine.nom') }}</title>
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
<body class="relative min-h-screen overflow-x-hidden bg-app px-4 py-16 text-slate-700 antialiased">

<div class="pointer-events-none absolute inset-x-0 top-0 hidden h-96 dark:block"
     style="background: radial-gradient(60% 100% at 50% 0%, rgba(139, 92, 246, .32), transparent 70%)"></div>

<a href="{{ route('accueil') }}"
   class="absolute left-4 top-4 flex items-center gap-2 rounded-full px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 sm:left-6 sm:top-6">
    <span aria-hidden="true">←</span> Retour à l'accueil
</a>

<div class="relative mx-auto w-full max-w-lg">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white">
            <x-icon name="cube" class="h-6 w-6" />
        </div>
        <h1 class="text-3xl font-medium tracking-tight">
            <span class="bg-linear-to-r from-blue-500 via-violet-500 to-fuchsia-400 bg-clip-text text-transparent">Créer mon compte</span>
        </h1>
        <p class="mt-1 text-slate-500">Passez vos commandes et suivez vos colis.</p>
    </div>

    <form action="{{ route('register.attempt') }}" method="POST"
          class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf

        {{-- Champ piège pour les robots : invisible pour un humain --}}
        <div class="hidden" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        @error('website') <p class="rounded-xl bg-red-50 px-4 py-2 text-sm text-red-800">{{ $message }}</p> @enderror

        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ([['nom', 'Nom', 'text'], ['prenom', 'Prénom', 'text'], ['telephone', 'Téléphone', 'tel'], ['email', 'E-mail', 'email']] as [$champ, $label, $type])
                <div>
                    <label for="{{ $champ }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
                    <input type="{{ $type }}" id="{{ $champ }}" name="{{ $champ }}" value="{{ old($champ) }}" required
                           class="w-full rounded-xl border px-4 py-2.5 text-sm focus:outline-none
                                  {{ $errors->has($champ) ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">
                    @error($champ) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach

            <div class="sm:col-span-2">
                <label for="adresse" class="mb-1 block text-sm font-medium text-slate-700">Adresse (facultatif)</label>
                <input type="text" id="adresse" name="adresse" value="{{ old('adresse') }}"
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                @error('adresse') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Mot de passe</label>
                <input type="password" id="password" name="password" required minlength="8"
                       class="w-full rounded-xl border px-4 py-2.5 text-sm focus:outline-none
                              {{ $errors->has('password') ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">
                <p class="mt-1 text-xs text-slate-400">8 caractères minimum.</p>
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirmer</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
            </div>
        </div>

        <button class="w-full rounded-full bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
            Créer mon compte
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Déjà un compte ? <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:underline">Se connecter</a>
    </p>
</div>

</body>
</html>