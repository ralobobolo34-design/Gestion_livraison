<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titre', 'Dashboard') | Gestion</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <meta name="theme-color" content="#100d18">

    {{-- Appliqué avant l'affichage pour éviter un flash de couleur --}}
    <script>
        (function () {
            var racine = document.documentElement;
            try {
                if (localStorage.getItem('theme') !== 'light') racine.classList.add('dark');
                if (localStorage.getItem('sidebar') === 'reduite') racine.classList.add('sidebar-reduite');
            } catch (e) { racine.classList.add('dark'); }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-app text-slate-700 antialiased">

@php
    $user = auth()->user();

    $menu = collect([
        ['section' => 'Pilotage',       'label' => 'Dashboard',    'url' => url('/dashboard'),        'match' => 'dashboard*',  'icon' => 'home',          'roles' => ['admin', 'agent']],
        ['section' => 'Mon espace',     'label' => 'Accueil',      'url' => url('/mon-espace'),       'match' => 'mon-espace*', 'icon' => 'home',          'roles' => ['client']],
        ['section' => 'Mon espace',     'label' => 'Mes tournées', 'url' => url('/livreur/tournees'), 'match' => 'livreur*',    'icon' => 'map',           'roles' => ['livreur']],
        ['section' => 'Opérations',     'label' => 'Clients',      'url' => url('/clients'),          'match' => 'clients*',    'icon' => 'users',         'roles' => ['admin', 'agent']],
        ['section' => 'Opérations',     'label' => 'Commandes',    'url' => url('/commandes'),        'match' => 'commandes*',  'icon' => 'shopping-cart', 'roles' => ['admin', 'agent', 'client']],
        ['section' => 'Opérations',     'label' => 'Colis',        'url' => url('/colis'),            'match' => 'colis*',      'icon' => 'cube',          'roles' => ['admin', 'agent', 'client']],
        ['section' => 'Opérations',     'label' => 'Tournées',     'url' => url('/tournees'),         'match' => 'tournees*',   'icon' => 'map',           'roles' => ['admin', 'agent']],
        ['section' => 'Opérations',     'label' => 'Paiements',    'url' => url('/paiements'),        'match' => 'paiements*',  'icon' => 'credit-card',   'roles' => ['admin', 'agent', 'client']],
        ['section' => 'Configuration',  'label' => 'Livreurs',     'url' => url('/livreurs'),         'match' => 'livreurs*',   'icon' => 'truck',         'roles' => ['admin']],
        ['section' => 'Configuration',  'label' => 'Zones',        'url' => url('/zones'),            'match' => 'zones*',      'icon' => 'map-pin',       'roles' => ['admin']],
        ['section' => 'Configuration',  'label' => 'Tarifs',       'url' => url('/tarifs'),           'match' => 'tarifs*',     'icon' => 'tag',           'roles' => ['admin']],
    ])
        ->filter(fn ($item) => in_array($user->role, $item['roles'], true))
        ->groupBy('section');
@endphp

<div class="flex min-h-screen">

    {{-- Barre latérale --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-side
                  transition-transform dark:border-transparent
                  lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:self-start">

        <div class="sb-tete flex h-16 shrink-0 items-center gap-3 px-4">
            <div class="sb-logo flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white">
                    <x-icon name="cube" class="h-5 w-5" />
                </div>
                <span class="text-lg font-semibold text-slate-800">Gestion</span>
            </div>

            <button type="button" id="btn-reduire" aria-label="Réduire le menu" title="Réduire le menu"
                    class="ml-auto hidden rounded-full p-2 text-slate-500 hover:bg-slate-100 lg:flex">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6ZM9 3.75v16.5" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-4">
            @foreach ($menu as $section => $items)
                <p class="sb-texte px-4 pb-1 pt-4 text-xs font-medium text-slate-500">{{ $section }}</p>

                @foreach ($items as $item)
                    <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                       class="sb-lien flex items-center gap-3 rounded-full px-4 py-2.5 text-sm font-medium transition
                              {{ request()->is($item['match'])
                                  ? 'bg-slate-200/70 text-slate-900 dark:bg-white/10 dark:text-white'
                                  : 'text-slate-600 hover:bg-slate-100' }}">
                        <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                        <span class="sb-texte">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>

        {{-- Profil en bas --}}
        <div class="shrink-0 border-t border-slate-200 p-3 dark:border-white/5">
            <div class="sb-user flex items-center gap-3 px-1 py-1">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <div class="sb-texte min-w-0 flex-1 leading-tight">
                    <p class="truncate text-sm font-medium text-slate-800">{{ $user->name }}</p>
                    <p class="truncate text-xs capitalize text-slate-500">{{ $user->role }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button title="Déconnexion" aria-label="Déconnexion"
                            class="rounded-full p-2 text-slate-500 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="relative flex min-w-0 flex-1 flex-col overflow-hidden">

        {{-- Halo violet en haut de la page (thème sombre) --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 hidden h-96 dark:block"
             style="background: radial-gradient(60% 100% at 50% 0%, rgba(139, 92, 246, .28), transparent 70%)"></div>

        {{-- Barre du haut --}}
        <header class="relative flex h-16 items-center justify-between px-6">
            <div class="flex items-center gap-3">
                <button id="menu-btn" class="rounded-full p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-800">@yield('titre', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-2">
                @if ($user->isStaff())
                    <button type="button" id="btn-zones"
                            class="flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Zones
                    </button>
                @endif

                {{-- Changer de thème --}}
                <button type="button" id="btn-theme" aria-label="Changer de thème" title="Changer de thème"
                        class="rounded-full p-2 text-slate-500 hover:bg-slate-100">
                    <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <svg class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>
            </div>
        </header>

        {{-- Contenu --}}
        <main class="relative flex-1 p-6">

            @if (session('success'))
                <div class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <svg class="h-5 w-5 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <svg class="h-5 w-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('contenu')
        </main>

        <footer class="relative px-6 py-4 text-sm text-slate-500">
            © {{ date('Y') }} Gestion. Tous droits réservés.
        </footer>
    </div>
</div>

@if ($user->isStaff())
    {{-- Fenêtre de rappel des zones et quartiers --}}
    <dialog id="dialog-zones" class="m-auto w-full max-w-3xl rounded-2xl bg-white p-0 text-slate-700 shadow-xl backdrop:bg-black/60">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h2 class="text-base font-semibold text-slate-800">Zones et quartiers</h2>
            <div class="flex items-center gap-3">
                @if ($user->isAdmin())
                    <a href="{{ route('zones.index') }}" class="text-sm text-blue-600 hover:underline">Gérer les zones</a>
                @endif
                <button type="button" onclick="this.closest('dialog').close()"
                        class="rounded-full px-2 py-1 text-slate-500 hover:bg-slate-100" aria-label="Fermer">✕</button>
            </div>
        </div>
        <div id="zones-contenu" class="max-h-[70vh] overflow-y-auto p-6 text-sm text-slate-500">Chargement...</div>
    </dialog>
@endif

<script>
    const racine = document.documentElement;

    // Menu mobile
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => sidebar.classList.toggle('-translate-x-full'));
    }

    // Réduire / agrandir la barre latérale (ordinateur)
    document.getElementById('btn-reduire').addEventListener('click', () => {
        const reduite = racine.classList.toggle('sidebar-reduite');
        try { localStorage.setItem('sidebar', reduite ? 'reduite' : 'ouverte'); } catch (e) {}
    });

    // Thème clair / sombre
    document.getElementById('btn-theme').addEventListener('click', () => {
        const sombre = racine.classList.toggle('dark');
        try { localStorage.setItem('theme', sombre ? 'dark' : 'light'); } catch (e) {}
    });

    @if ($user->isStaff())
    // Rappel des zones
    const dialogZones = document.getElementById('dialog-zones');
    const contenuZones = document.getElementById('zones-contenu');
    let zonesChargees = false;

    document.getElementById('btn-zones').addEventListener('click', async () => {
        dialogZones.showModal();
        if (zonesChargees) return;

        try {
            const reponse = await fetch('{{ route('zones.rappel') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!reponse.ok) throw new Error();
            contenuZones.innerHTML = await reponse.text();
            zonesChargees = true;
        } catch (e) {
            contenuZones.innerHTML = '<p class="text-sm text-red-600">Impossible de charger les zones.</p>';
        }
    });

    dialogZones.addEventListener('click', (e) => { if (e.target === dialogZones) dialogZones.close(); });

    dialogZones.addEventListener('input', (e) => {
        if (e.target.id !== 'rappel-recherche') return;
        const terme = e.target.value.trim().toLowerCase();

        dialogZones.querySelectorAll('.rappel-zone').forEach((zone) => {
            const nomZone = zone.querySelector('h3').textContent.toLowerCase();
            const zoneCorrespond = terme !== '' && nomZone.includes(terme);
            let visible = false;

            zone.querySelectorAll('.rappel-quartier').forEach((q) => {
                const correspond = terme === '' || zoneCorrespond || q.textContent.toLowerCase().includes(terme);
                q.classList.toggle('hidden', !correspond);
                q.classList.toggle('ring-2', terme !== '' && !zoneCorrespond && correspond);
                q.classList.toggle('ring-blue-400', terme !== '' && !zoneCorrespond && correspond);
                if (correspond) visible = true;
            });

            zone.classList.toggle('hidden', !visible && !zoneCorrespond);
        });
    });
    @endif
</script>

</body>
</html>