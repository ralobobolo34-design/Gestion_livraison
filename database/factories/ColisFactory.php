<?php

namespace Database\Factories;

use App\Models\Colis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Colis>
 */
class ColisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array
{
    return [
        'description' => fake()->randomElement(['Vêtements', 'Documents', 'Électronique', 'Alimentaire', 'Pièces auto']),
        'poids' => fake()->randomFloat(2, 0.2, 10),
        'dimensions' => fake()->numerify('## x ## x ## cm'),
        'valeur_declaree' => fake()->numberBetween(10, 500) * 1000,
        'fragile' => fake()->boolean(25),
    ];
}
}
