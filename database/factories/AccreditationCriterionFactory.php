<?php

namespace Database\Factories;

use App\Models\AccreditationCriterion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccreditationCriterion>
 */
class AccreditationCriterionFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        return [
            'code' => 'K'.$counter.fake()->unique()->randomNumber(3),
            'name' => 'Kriteria '.fake()->words(3, true),
            'instrument' => 'LAMEMBA',
            'sort_order' => $counter,
        ];
    }
}
