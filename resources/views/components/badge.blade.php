@props(['statut'])

@php
    $couleur = match ($statut) {
        'LIVREE', 'LIVRE', 'PAYE', 'ACTIF'                                  => 'bg-green-100 text-green-700',
        'ANNULEE', 'ANNULE', 'ECHEC_LIVRAISON'                              => 'bg-red-100 text-red-700',
        'EN_ATTENTE', 'CREE', 'PARTIEL', 'INDISPONIBLE'                     => 'bg-yellow-100 text-yellow-700',
        'EN_LIVRAISON', 'EN_TRANSIT', 'CONFIRMEE', 'EN_PREPARATION',
        'PRIS_EN_CHARGE', 'ARRIVE_AGENCE'                                   => 'bg-blue-100 text-blue-700',
        default                                                             => 'bg-slate-200 text-slate-600',
    };
@endphp

<span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $couleur }}">
    {{ str_replace('_', ' ', $statut) }}
</span>