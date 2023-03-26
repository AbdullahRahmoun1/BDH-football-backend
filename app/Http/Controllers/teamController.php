<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\ItemNotFoundException;

class teamController extends Controller
{
    public function show($id)
    {
        $team=Team::with('players:id,team_id,name,position')
        ->findOrFail($id);
        $players=$team->players;
        $players->makeHidden(['team_id','position']);
        $captain=$players->firstWhere('position',Player::CAPTAIN);
        $goalKeeper=$players->firstWhere('position',Player::GOAL_KEEPER);
        $players=$players->whereNotIn('id',[$captain->id,$goalKeeper->id])->all();
        $team->attackers=array_values($players);
        $team->captain=$captain;
        $team->goalKeeper=$goalKeeper;
        $team->makeHidden('players');
        return $team;
    }
}
