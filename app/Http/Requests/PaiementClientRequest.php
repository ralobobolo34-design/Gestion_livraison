<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaiementClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isClient() && $this->user()->client !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('reference_transaction')) {
            $this->merge(['reference_transaction' => strtoupper(trim((string) $this->input('reference_transaction')))]);
        }
    }

    public function rules(): array
    {
        return [
            'montant'               => ['required', 'integer', 'min:1'],
            'mode'                  => ['required', Rule::in(['MOBILE_MONEY', 'PAIEMENT_LIVRAISON'])],
            'operateur'             => ['required_if:mode,MOBILE_MONEY', 'nullable', Rule::in(['MVOLA', 'ORANGE_MONEY', 'AIRTEL_MONEY'])],
            // La référence reçue par SMS ne peut servir qu'une fois
            'reference_transaction' => ['required_if:mode,MOBILE_MONEY', 'nullable', 'string', 'min:6', 'max:40',
                                        'regex:/^[A-Za-z0-9._-]+$/', 'unique:paiements,reference_transaction'],
        ];
    }

    public function messages(): array
    {
        return [
            'operateur.required_if'             => 'Choisissez votre opérateur Mobile Money.',
            'reference_transaction.required_if' => 'Saisissez la référence de la transaction reçue par SMS.',
            'reference_transaction.regex'       => 'La référence ne doit contenir que des lettres, des chiffres, des points et des tirets.',
            'reference_transaction.unique'      => 'Cette référence de transaction a déjà été déclarée.',
            'reference_transaction.min'         => 'La référence de transaction semble trop courte.',
        ];
    }
}