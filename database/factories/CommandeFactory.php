<?php

namespace Database\Factories;

use App\Models\Commande;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commande>
 */
class CommandeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    $date = fake()->dateTimeBetween('-60 days', 'now');

    return [
        'adresse_depart' => fake()->streetAddress(),
        'adresse_destination' => fake()->streetAddress(),
        'type_livraison' => fake()->randomElement(['STANDARD', 'STANDARD', 'EXPRESS']),
        'priorite' => fake()->randomElement(['NORMALE', 'NORMALE', 'URGENTE']),
        'date_commande' => $date,
        'date_livraison_prevue' => (clone $date)->modify('+3 days'),
        'statut' => fake()->randomElement([
            'EN_ATTENTE', 'CONFIRMEE', 'EN_PREPARATION',
            'EN_LIVRAISON', 'LIVREE', 'LIVREE', 'ANNULEE',
        ]),
    ];
}
}
