@extends('layouts.app')

@section('titre', 'Payer ' . $commande->reference)

@section('contenu')
@php
    $numeros = [
        'MVOLA'        => config('vitrine.paiement.mvola'),
        'ORANGE_MONEY' => config('vitrine.paiement.orange_money'),
        'AIRTEL_MONEY' => config('vitrine.paiement.airtel_money'),
    ];
    $enVerification = (int) $commande->paiements->where('statut', 'EN_ATTENTE')->sum('montant');
    $mode = old('mode', 'MOBILE_MONEY');
@endphp

<div class="max-w-3xl space-y-6">

    {{-- Récapitulatif --}}
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 text-base font-semibold text-slate-800">Commande {{ $commande->reference }}</h2>
        <dl class="grid gap-4 text-sm sm:grid-cols-4">
            <div><dt class="text-slate-500">Montant total</dt><dd class="font-medium">{{ number_format($commande->montant, 0, ',', ' ') }} Ar</dd></div>
            <div><dt class="text-slate-500">Déjà encaissé</dt><dd class="font-medium">{{ number_format($commande->montantPaye(), 0, ',', ' ') }} Ar</dd></div>
            <div><dt class="text-slate-500">En vérification</dt><dd class="font-medium">{{ number_format($enVerification, 0, ',', ' ') }} Ar</dd></div>
            <div><dt class="text-slate-500">Reste à régler</dt><dd class="text-lg font-semibold text-blue-600">{{ number_format($restant, 0, ',', ' ') }} Ar</dd></div>
        </dl>
    </div>

    <form action="{{ route('paiements.declarer', $commande) }}" method="POST"
          class="space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf

        {{-- Choix du mode --}}
        <div>
            <p class="mb-2 text-sm font-medium text-slate-700">Comment souhaitez-vous payer ?</p>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="cursor-pointer rounded-xl border border-slate-300 p-4 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                    <input type="radio" name="mode" value="MOBILE_MONEY" class="mr-2" @checked($mode === 'MOBILE_MONEY')>
                    <span class="font-medium text-slate-800">Mobile Money</span>
                    <span class="mt-1 block text-xs text-slate-500">MVola, Orange Money ou Airtel Money</span>
                </label>
                <label class="cursor-pointer rounded-xl border border-slate-300 p-4 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                    <input type="radio" name="mode" value="PAIEMENT_LIVRAISON" class="mr-2" @checked($mode === 'PAIEMENT_LIVRAISON')>
                    <span class="font-medium text-slate-800">Payer à la livraison</span>
                    <span class="mt-1 block text-xs text-slate-500">En espèces, remis au livreur</span>
                </label>
            </div>
            @error('mode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Mobile Money --}}
        <div id="panneau-mobile" class="space-y-4 rounded-xl bg-slate-50 p-4">
            <ol class="list-decimal space-y-1 pl-5 text-sm text-slate-600">
                <li>Choisissez votre opérateur.</li>
                <li>Envoyez le montant au numéro affiché, au nom de <strong>{{ config('vitrine.paiement.beneficiaire') }}</strong>.</li>
                <li>Recopiez ci-dessous la <strong>référence de la transaction</strong> reçue par SMS.</li>
                <li>Notre équipe vérifie la transaction et confirme votre paiement.</li>
            </ol>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="operateur" class="mb-1 block text-sm font-medium text-slate-700">Opérateur</label>
                    <select id="operateur" name="operateur" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">-- Choisir --</option>
                        @foreach (['MVOLA' => 'MVola', 'ORANGE_MONEY' => 'Orange Money', 'AIRTEL_MONEY' => 'Airtel Money'] as $code => $libelle)
                            <option value="{{ $code }}" @selected(old('operateur') === $code)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                    @error('operateur') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p id="numero-operateur" class="mt-2 hidden text-sm text-slate-600">
                        Envoyez le montant au : <strong id="numero-valeur" class="text-slate-800"></strong>
                    </p>
                </div>

                <div>
                    <label for="reference_transaction" class="mb-1 block text-sm font-medium text-slate-700">Référence de la transaction</label>
                    <input type="text" id="reference_transaction" name="reference_transaction" value="{{ old('reference_transaction') }}"
                           autocomplete="off" placeholder="Ex. MP240101.1234.A12345"
                           class="w-full rounded-lg border px-3 py-2 text-sm {{ $errors->has('reference_transaction') ? 'border-red-400' : 'border-slate-300' }}">
                    @error('reference_transaction') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Paiement à la livraison --}}
        <div id="panneau-livraison" class="hidden rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
            Vous réglerez ce montant en espèces au livreur au moment de la remise du colis.
            Le paiement sera confirmé dès que l'argent aura été encaissé.
        </div>

        {{-- Montant --}}
        <div>
            <label for="montant" class="mb-1 block text-sm font-medium text-slate-700">Montant à régler (Ar)</label>
            <input type="number" id="montant" name="montant" min="1" max="{{ $restant }}" value="{{ old('montant', $restant) }}"
                   class="w-full rounded-lg border px-3 py-2 text-sm sm:max-w-xs {{ $errors->has('montant') ? 'border-red-400' : 'border-slate-300' }}">
            <p class="mt-1 text-xs text-slate-400">Vous pouvez payer en plusieurs fois. Maximum : {{ number_format($restant, 0, ',', ' ') }} Ar.</p>
            @error('montant') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <p class="text-xs text-slate-400">
            Paiement par carte bancaire ou en espèces à l'agence : contactez-nous au {{ config('vitrine.telephone') }}.
        </p>

        <div class="flex gap-3">
            <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Valider mon paiement</button>
            <a href="{{ route('commandes.show', $commande) }}" class="rounded-lg px-5 py-2 text-sm text-slate-600 hover:bg-slate-100">Annuler</a>
        </div>
    </form>
</div>

<script>
    const numeros = @json($numeros);
    const panneauMobile = document.getElementById('panneau-mobile');
    const panneauLivraison = document.getElementById('panneau-livraison');
    const selectOperateur = document.getElementById('operateur');
    const ligneNumero = document.getElementById('numero-operateur');
    const valeurNumero = document.getElementById('numero-valeur');

    function majMode() {
        const mode = document.querySelector('[name=mode]:checked').value;
        panneauMobile.classList.toggle('hidden', mode !== 'MOBILE_MONEY');
        panneauLivraison.classList.toggle('hidden', mode !== 'PAIEMENT_LIVRAISON');
    }

    function majNumero() {
        const numero = numeros[selectOperateur.value];
        ligneNumero.classList.toggle('hidden', !numero);
        valeurNumero.textContent = numero || '';
    }

    document.querySelectorAll('[name=mode]').forEach((r) => r.addEventListener('change', majMode));
    selectOperateur.addEventListener('change', majNumero);
    majMode();
    majNumero();
</script>
@endsection