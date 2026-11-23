@extends('layouts.app')

@section('titre', 'Dashboard')


@section('contenu')
<div class="mx-auto mb-8 max-w-3xl pt-2 text-center">
    <h2 class="text-4xl font-medium tracking-tight">
        <span class="bg-linear-to-r from-blue-500 via-violet-500 to-fuchsia-400 bg-clip-text text-transparent">
            Bonjour {{ auth()->user()->name }}
        </span>
    </h2>
    <p class="mt-2 text-xl text-slate-500">Que souhaitez-vous suivre aujourd'hui ?</p>

    <form action="{{ route('colis.index') }}" method="GET" class="mt-6">
        <div class="flex items-center gap-3 rounded-full border border-slate-300 bg-white px-5 py-3 shadow-sm focus-within:border-blue-500">
            <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="search" name="q" placeholder="Suivre un colis : référence, client ou téléphone"
                   class="w-full bg-transparent text-sm placeholder:text-slate-400 focus:outline-none">
            <button class="rounded-full bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700">Rechercher</button>
        </div>
    </form>
</div>

@php
    $cartes = [
        ['Clients',            $stats['clients'],            'text-slate-800', route('clients.index')],
        ['Livreurs',           $stats['livreurs'],           'text-slate-800', route('livreurs.index'), $stats['livreurs_actifs'] . ' actifs'],
        ['Commandes',          $stats['commandes'],          'text-slate-800', route('commandes.index')],
        ['Colis',              $stats['colis'],              'text-slate-800', route('colis.index')],
        ['Colis en livraison', $stats['colis_en_livraison'], 'text-blue-600',  route('colis.index', ['statut' => 'EN_LIVRAISON'])],
        ['Colis livrés',       $stats['colis_livres'],       'text-green-600', route('colis.index', ['statut' => 'LIVRE'])],
        ['Colis en retard',    $stats['colis_en_retard'],    $stats['colis_en_retard'] > 0 ? 'text-red-600' : 'text-slate-800', null, 'date prévue dépassée'],
        ['Commandes annulées', $stats['commandes_annulees'], 'text-slate-800', route('commandes.index', ['statut' => 'ANNULEE'])],
    ];
@endphp

{{-- Cartes --}}
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($cartes as $carte)
        @php
            [$label, $valeur, $couleur, $lien] = $carte;
            $note = $carte[4] ?? null;
        @endphp
        <{{ $lien ? 'a href=' . $lien : 'div' }} class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 {{ $lien ? 'transition hover:ring-blue-300' : '' }}">
            <p class="text-sm text-slate-500">{{ $label }}</p>
            <p class="mt-1 text-3xl font-bold {{ $couleur }}">{{ number_format($valeur, 0, ',', ' ') }}</p>
            @if ($note)
                <p class="mt-1 text-xs text-slate-400">{{ $note }}</p>
            @endif
        </{{ $lien ? 'a' : 'div' }}>
    @endforeach
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-2">
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <p class="text-sm text-slate-500">Montant total des ventes</p>
        <p class="mt-1 text-3xl font-bold text-slate-800">{{ number_format($stats['ventes'], 0, ',', ' ') }} Ar</p>
        <p class="mt-1 text-xs text-slate-400">Commandes non annulées</p>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <p class="text-sm text-slate-500">Paiements encaissés</p>
        <p class="mt-1 text-3xl font-bold text-green-600">{{ number_format($stats['paiements'], 0, ',', ' ') }} Ar</p>
       <p class="mt-1 text-xs text-slate-400">
    <a href="{{ route('paiements.index', ['statut' => 'EN_ATTENTE']) }}" class="hover:underline">{{ $stats['a_verifier'] }} paiement(s) à vérifier</a>
</p>
    </div>
</div>

{{-- Graphiques --}}
<div class="mb-6 grid gap-4 lg:grid-cols-2">
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 text-sm font-semibold text-slate-800">Livraisons par jour (30 derniers jours)</h2>
        <div class="relative h-64"><canvas id="graphJours"></canvas></div>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 text-sm font-semibold text-slate-800">Revenus par mois</h2>
        <div class="relative h-64"><canvas id="graphMois"></canvas></div>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 text-sm font-semibold text-slate-800">Colis par statut</h2>
        <div class="relative h-64"><canvas id="graphStatuts"></canvas></div>
    </div>
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 text-sm font-semibold text-slate-800">Performance des livreurs</h2>
        <div class="relative h-64"><canvas id="graphLivreurs"></canvas></div>
    </div>
</div>

{{-- Dernières commandes --}}
<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="flex items-center justify-between border-b border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-800">Dernières commandes</h2>
        <a href="{{ route('commandes.index') }}" class="text-sm text-blue-600 hover:underline">Tout voir</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Référence</th>
                    <th class="px-5 py-3">Client</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3 text-right">Montant</th>
                    <th class="px-5 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($dernieresCommandes as $commande)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">
                            <a href="{{ route('commandes.show', $commande) }}" class="hover:underline">{{ $commande->reference }}</a>
                        </td>
                        <td class="px-5 py-3">{{ $commande->client->nom }} {{ $commande->client->prenom }}</td>
                        <td class="px-5 py-3">{{ $commande->date_commande->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($commande->montant, 0, ',', ' ') }} Ar</td>
                        <td class="px-5 py-3"><x-badge :statut="$commande->statut" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Aucune commande.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const g = @json($graphiques);
    const ar = (v) => v.toLocaleString('fr-FR') + ' Ar';
    const sombre = document.documentElement.classList.contains('dark');
Chart.defaults.color = sombre ? '#c2b6d2' : '#64748b';
Chart.defaults.borderColor = sombre ? 'rgba(255, 255, 255, 0.08)' : '#e2e8f0';
Chart.defaults.elements.arc.borderColor = sombre ? '#1b1626' : '#ffffff';

    new Chart(document.getElementById('graphJours'), {
        type: 'bar',
        data: {
            labels: g.jours.labels,
            datasets: [{ label: 'Colis livrés', data: g.jours.data, backgroundColor: '#7c3aed', borderRadius: 4 }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    new Chart(document.getElementById('graphMois'), {
        type: 'bar',
        data: {
            labels: g.mois.labels,
            datasets: [
                { label: 'Ventes',    data: g.mois.ventes,   backgroundColor: '#7c3aed', borderRadius: 4 },
                { label: 'Encaissé',  data: g.mois.encaisse, backgroundColor: '#16a34a', borderRadius: 4 },
            ],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { tooltip: { callbacks: { label: (c) => c.dataset.label + ' : ' + ar(c.parsed.y) } } },
            scales: { y: { beginAtZero: true, ticks: { callback: (v) => v.toLocaleString('fr-FR') } } },
        },
    });

    new Chart(document.getElementById('graphStatuts'), {
        type: 'doughnut',
        data: {
            labels: g.statuts.labels,
            datasets: [{
                data: g.statuts.data,
                backgroundColor: ['#eab308', '#38bdf8', '#6366f1', '#8b5cf6', '#2563eb', '#16a34a', '#f97316', '#ef4444'],
            }],
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } },
    });

    new Chart(document.getElementById('graphLivreurs'), {
        type: 'bar',
        data: {
            labels: g.livreurs.labels,
            datasets: [
                { label: 'Colis affectés', data: g.livreurs.affectes, backgroundColor: '#94a3b8', borderRadius: 4 },
                { label: 'Colis livrés',   data: g.livreurs.livres,   backgroundColor: '#16a34a', borderRadius: 4 },
            ],
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
</script>
@endsection