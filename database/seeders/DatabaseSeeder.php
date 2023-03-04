<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Contest;
use App\Models\Player;
use App\Models\RedCard;
use App\Models\Team;
use App\Models\YellowCard;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Player::factory(30)->create();
        Contest::factory(10)->create();
        RedCard::factory(10)->create();
        YellowCard::factory(10)->create();
    }
}

