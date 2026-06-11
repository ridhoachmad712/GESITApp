<?php

namespace Database\Factories;

use App\Models\Lecturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lecturer>
 */
class LecturerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nidn' => fake()->unique()->numerify('00########'),
            'position' => 'Lektor',
            'expertise' => fake()->randomElement(['Manajemen Keuangan', 'Manajemen Pemasaran', 'Manajemen SDM', 'Manajemen Operasional']),
            'email' => fake()->unique()->safeEmail(),
            'photo_path' => null,
            'publication_url' => 'https://sinta.kemdikbud.go.id/authors/profile/'.fake()->numberBetween(100000, 999999),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
