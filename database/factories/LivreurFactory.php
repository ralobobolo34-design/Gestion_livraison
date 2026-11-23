<?php

namespace Database\Factories;

use App\Models\Livreur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Livreur>
 */
class LivreurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array
{
    return [
        'nom' => fake()->lastName(),
        'prenom' => fake()->firstName(),
        'telephone' => fake()->numerify('032 ## ### ##'),
        'email' => fake()->unique()->safeEmail(),
        'type_vehicule' => fake()->randomElement(['MOTO', 'VOITURE', 'CAMION', 'AUTRE']),
        'numero_vehicule' => fake()->numerify('#### TAA'),
        'statut' => fake()->randomElement(['ACTIF', 'ACTIF', 'ACTIF', 'INACTIF', 'INDISPONIBLE']),
        'date_inscription' => fake()->dateTimeBetween('-2 years', 'now'),
    ];
}
}
