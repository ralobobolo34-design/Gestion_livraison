<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TourneeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // les rôles seront gérés plus tard
    }

    public function rules(): array
    {
        return [
            'livreur_id'   => ['required', Rule::exists('livreurs', 'id')->where('statut', 'ACTIF')],
            'date'         => ['required', 'date', 'after_or_equal:today'],
            'heure_depart' => ['required', 'date_format:H:i'],
            'heure_fin'    => ['required', 'date_format:H:i', 'after:heure_depart'],
        ];
    }

    public function messages(): array
    {
        return [
            'livreur_id.exists'  => 'Le livreur doit exister et être ACTIF.',
            'heure_fin.after'    => 'L\'heure de fin doit être après l\'heure de départ.',
            'date.after_or_equal' => 'La date ne peut pas être dans le passé.',
        ];
    }
}