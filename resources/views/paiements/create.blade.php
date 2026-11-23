@extends('layouts.app')

@section('titre', 'Nouveau paiement')

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('paiements.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="commande_id" class="mb-1 block text-sm font-medium text-slate-700">Commande</label>
            <select id="commande_id" name="commande_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">-- Choisir --</option>
                @foreach ($commandes as $commande)
                    <option value="{{ $commande->id }}" data-restant="{{ $commande->montantRestant() }}"
                            @selected(old('commande_id', request('commande_id')) == $commande->id)>
                        {{ $commande->reference }} · {{ $commande->client->nom }} {{ $commande->client->prenom }}
                        · reste {{ number_format($commande->montantRestant(), 0, ',', ' ') }} Ar
                    </option>
                @endforeach
            </select>
            @error('commande_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="montant" class="mb-1 block text-sm font-medium text-slate-700">Montant (Ar)</label>
                <input type="number" min="1" id="montant" name="montant" value="{{ old('montant') }}"
                       class="w-full rounded-lg border px-3 py-2 text-sm {{ $errors->has('montant') ? 'border-red-400' : 'border-slate-300' }}">
                <p id="aide-restant" class="mt-1 text-xs text-slate-400"></p>
                @error('montant') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="mode" class="mb-1 block text-sm font-medium text-slate-700">Mode de paiement</label>
                <select id="mode" name="mode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (['ESPECES', 'MOBILE_MONEY', 'CARTE', 'PAIEMENT_LIVRAISON'] as $m)
                        <option value="{{ $m }}" @selected(old('mode', 'ESPECES') === $m)>{{ str_replace('_', ' ', $m) }}</option>
                    @endforeach
                </select>
                @error('mode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="date_paiement" class="mb-1 block text-sm font-medium text-slate-700">Date et heure</label>
                <input type="datetime-local" id="date_paiement" name="date_paiement"
                       value="{{ old('date_paiement', now()->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('date_paiement') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <p class="text-sm text-slate-500">
            Un paiement en « PAIEMENT LIVRAISON » reste en attente jusqu'à sa confirmation.
        </p>

        <div class="flex gap-3">
            <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Enregistrer</button>
            <a href="{{ route('paiements.index') }}" class="rounded-lg px-5 py-2 text-sm text-slate-600 hover:bg-slate-100">Annuler</a>
        </div>
    </form>
</div>

<script>
    const select = document.getElementById('commande_id');
    const montant = document.getElementById('montant');
    const aide = document.getElementById('aide-restant');

    function majRestant() {
        const option = select.options[select.selectedIndex];
        const restant = option && option.dataset.restant;
        if (!restant) { aide.textContent = ''; return; }
        aide.textContent = 'Reste à régler : ' + Number(restant).toLocaleString('fr-FR') + ' Ar';
        if (!montant.value) montant.value = restant;
        montant.max = restant;
    }

    select.addEventListener('change', () => { montant.value = ''; majRestant(); });
    majRestant();
</script>
@endsection