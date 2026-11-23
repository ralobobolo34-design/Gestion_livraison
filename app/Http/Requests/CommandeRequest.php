<?php

namespace App\Http\Requests;

use App\Models\Commande;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CommandeRequest extends FormRequest
{
    // Nombre maximum de commandes en attente pour un même client
    public const MAX_EN_ATTENTE = 5;

    // Seul un client ayant un profil peut passer une commande
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->isClient() && $user->client !== null;
    }

    // Le client commande toujours en son nom : tout client_id envoyé est écrasé
    protected function prepareForValidation(): void
    {
        $this->merge(['client_id' => $this->user()?->client?->id]);
    }

    public function rules(): array
    {
        return [
            'client_id'             => ['required', Rule::exists('clients', 'id')->where('statut', 'ACTIF')],
            'quartier_id'           => ['required', Rule::exists('quartiers', 'id')->where(
                fn ($query) => $query->whereIn('zone_id', fn ($sub) => $sub->select('id')->from('zones')->where('statut', 'ACTIF'))
            )],
            'adresse_depart'        => ['required', 'string', 'max:255'],
            'adresse_destination'   => ['required', 'string', 'max:255'],
            'type_livraison'        => ['required', Rule::in(['STANDARD', 'EXPRESS'])],
            'priorite'              => ['required', Rule::in(['NORMALE', 'URGENTE'])],
            'date_livraison_prevue' => ['nullable', 'date', 'after_or_equal:today'],

            'colis'                    => ['required', 'array', 'min:1', 'max:10'],
            'colis.*.description'      => ['nullable', 'string', 'max:255'],
            'colis.*.poids'            => ['required', 'numeric', 'min:0.01', 'max:10'],
            'colis.*.dimensions'       => ['nullable', 'string', 'max:50'],
            'colis.*.valeur_declaree'  => ['nullable', 'integer', 'min:0'],
            'colis.*.fragile'          => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.exists'    => 'Votre compte client doit être actif pour passer une commande.',
            'quartier_id.exists'  => 'Choisissez un quartier appartenant à une zone active.',
            'colis.required'      => 'Ajoutez au moins un colis.',
            'colis.max'           => 'Une commande ne peut pas contenir plus de 10 colis.',
            'colis.*.poids.max'   => 'Le poids maximum par colis est de 10 kg.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $client = $this->user()->client;

            if (! $client) {
                return;
            }

            $enAttente = Commande::where('client_id', $client->id)
                ->where('statut', 'EN_ATTENTE')
                ->count();

            if ($enAttente >= self::MAX_EN_ATTENTE) {
                $v->errors()->add('client_id', 'Vous avez déjà ' . $enAttente
                    . ' commandes en attente de confirmation. Attendez leur traitement avant d\'en passer une nouvelle.');
            }
        });
    }
}