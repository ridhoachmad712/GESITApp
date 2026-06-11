<?php

namespace Database\Factories;

use App\Models\Agreement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agreement>
 */
class AgreementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Kerja Sama '.fake()->unique()->company(),
            'partner_name' => fake()->company(),
            'type' => fake()->randomElement(['MoU', 'MoA', 'IA']),
            'start_date' => now()->subYear(),
            'end_date' => now()->addYears(2),
            'description' => fake()->sentence(),
            'document_id' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subYears(3),
            'end_date' => now()->subMonth(),
        ]);
    }
}
