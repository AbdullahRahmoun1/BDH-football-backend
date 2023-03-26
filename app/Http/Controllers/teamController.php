<?php
namespace App\Http\Controllers;
require_once("../settings/constants.php");
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\ItemNotFoundException;
use Constants;
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
    public function gradesAndClassesSuggestions(){
        $suggesions=[];
        $grades=Team::select('grade')
        ->distinct()
        ->orderBy("grade")
        ->get()->pluck("grade");
        foreach ($grades as  $grade) {
            $classes=Team::select('class')
            ->where('grade',$grade)
            ->distinct()
            ->orderBy("class")
            ->get()->pluck("class");
            foreach($classes as $class){
                $suggesions[]="Grade ".$grade." Class ".$class;
            }
        }
        return $suggesions;
    }
    public function part1Teams(){
        
    }
    public function part2Teams(){
        return Team::select('id','name','logo','points')
        ->where('stage',Constants::LEVEL1)
        ->orderByDesc('points')
        ->get();
    }

}
