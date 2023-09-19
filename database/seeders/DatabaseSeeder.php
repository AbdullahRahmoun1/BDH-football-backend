<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Http\Controllers\LeagueController;
use App\Models\Team;
use App\Models\User;
use App\Models\Player;
use App\Models\Contest;
use App\Models\RedCard;
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
        $user=User::create(['userName'=>'BDH_Admin','password'=>'1v4YuY9p987B']);
        $user->owner_type=config('consts.admin');
        $user->save();
        $user=User::create(['userName'=>'BDH_Admin_2','password'=>'6X1o0uvciO17']);
        $user->owner_type=config('consts.admin');
        $user->save();
        $user=User::create(['userName'=>'BDH_Admin_3','password'=>'1oijY6H5Xu35']);
        $user->owner_type=config('consts.admin');
        $user->save();
        LeagueController::updateInSettingsFile([
            'currentStage' => 0,
            'autoMatchMakingDone' => false
        ]);
    }
}

