<?php

namespace Database\Factories;

use App\Models\Contest;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RedCard>
 */
class RedCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'player_id'=>Player::factory()->create(),
            'contest_id'=>Contest::factory()->create(),
        ];
    }
}
