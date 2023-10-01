<?php

namespace Database\Factories;

use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contest>
 */
class ContestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'firstTeam_id'=>Team::factory()->create(),
            'secondTeam_id'=>Team::factory()->create(),
            'date'=>Carbon::now()->addDay(),
        ];
    }
}
