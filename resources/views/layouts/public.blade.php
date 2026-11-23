<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titre', config('vitrine.nom'))</title>
    <meta name="description" content="{{ config('vitrine.slogan') }}">
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
<body class="bg-app text-slate-700 antialiased">

@php
    $user = auth()->user();
    $accueil = route('accueil');
    $liens = [
        ['Accueil',         $accueil . '#accueil'],
        ['Nos services',    $accueil . '#services'],
        ['Tarifs',          $accueil . '#tarifs'],
        ['Suivi de colis',  route('suivi')],
    ];
@endphp

{{-- Halo violet en haut de la page (thème sombre) --}}
<div class="pointer-events-none absolute inset-x-0 top-0 -z-10 hidden h-[34rem] dark:block"
     style="background: radial-gradient(60% 100% at 50% 0%, rgba(139, 92, 246, .3), transparent 70%)"></div>

<header class="sticky top-0 z-30 border-b border-slate-200/60 bg-app/80 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">

        <a href="{{ $accueil }}" class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white">
                <x-icon name="cube" class="h-5 w-5" />
            </span>
            <span class="text-lg font-semibold text-slate-800">{{ config('vitrine.nom') }}</span>
        </a>

        <nav class="hidden items-center gap-1 md:flex">
            @foreach ($liens as [$libelle, $lien])
                <a href="{{ $lien }}" class="rounded-full px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">{{ $libelle }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <button type="button" id="btn-theme" aria-label="Changer de thème" title="Changer de thème"
                    class="rounded-full p-2 text-slate-500 hover:bg-slate-100">
                <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
                <svg class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </button>

            @if ($user)
                <a href="{{ $user->homeUrl() }}"
                   class="rounded-full bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Mon espace</a>
            @else
                <a href="{{ route('login') }}"
                   class="rounded-full bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Espace client</a>
            @endif

            <button type="button" id="btn-menu" aria-label="Menu" class="rounded-full p-2 text-slate-600 hover:bg-slate-100 md:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <div id="menu-mobile" class="hidden border-t border-slate-200/60 px-4 py-3 md:hidden">
        @foreach ($liens as [$libelle, $lien])
            <a href="{{ $lien }}" class="block rounded-xl px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-100">{{ $libelle }}</a>
        @endforeach
    </div>
</header>

<main>
    @yield('contenu')
</main>

<footer class="mt-24 border-t border-slate-200/60">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:grid-cols-3 sm:px-6">
        <div>
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white">
                    <x-icon name="cube" class="h-5 w-5" />
                </span>
                <span class="text-lg font-semibold text-slate-800">{{ config('vitrine.nom') }}</span>
            </div>
            <p class="mt-3 text-sm text-slate-500">{{ config('vitrine.slogan') }}</p>
        </div>

        <div>
            <p class="mb-3 text-sm font-semibold text-slate-800">Liens</p>
            <ul class="space-y-2 text-sm text-slate-500">
                @foreach ($liens as [$libelle, $lien])
                    <li><a href="{{ $lien }}" class="hover:text-slate-800">{{ $libelle }}</a></li>
                @endforeach
                <li><a href="{{ route('login') }}" class="hover:text-slate-800">Connexion</a></li>
            </ul>
        </div>

        <div id="contact">
            <p class="mb-3 text-sm font-semibold text-slate-800">Contact</p>
            <ul class="space-y-2 text-sm text-slate-500">
                <li><a href="mailto:{{ config('vitrine.email') }}" class="hover:text-slate-800">{{ config('vitrine.email') }}</a></li>
                <li>{{ config('vitrine.telephone') }}</li>
                <li>{{ config('vitrine.adresse') }}</li>
            </ul>
        </div>
    </div>
    <p class="border-t border-slate-200/60 py-5 text-center text-xs text-slate-400">
        © {{ date('Y') }} {{ config('vitrine.nom') }}. Tous droits réservés.
    </p>
</footer>

<script>
    document.getElementById('btn-theme').addEventListener('click', () => {
        const sombre = document.documentElement.classList.toggle('dark');
        try { localStorage.setItem('theme', sombre ? 'dark' : 'light'); } catch (e) {}
    });

    document.getElementById('btn-menu').addEventListener('click', () => {
        document.getElementById('menu-mobile').classList.toggle('hidden');
    });
</script>

</body>
</html>