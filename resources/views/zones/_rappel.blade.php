<input type="search" id="rappel-recherche" placeholder="Rechercher un quartier ou une zone..."
       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">

<div class="mt-4 grid gap-4 sm:grid-cols-2">
    @forelse ($zones as $zone)
        <div class="rappel-zone rounded-lg border border-slate-200 p-4">
            <div class="mb-1 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">{{ $zone->nom }}</h3>
                <span class="text-xs text-slate-500">{{ $zone->quartiers->count() }} quartiers</span>
            </div>
            <p class="mb-3 text-xs text-slate-500">Tarif de base : {{ number_format($zone->tarif_base, 0, ',', ' ') }} Ar</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach ($zone->quartiers as $quartier)
                    <span class="rappel-quartier rounded-full bg-slate-100 px-2.5 py-0.5 text-xs text-slate-700">{{ $quartier->nom }}</span>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-sm text-slate-500">Aucune zone active.</p>
    @endforelse
</div>