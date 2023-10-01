<?php
namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class playerController extends Controller
{
    public function playersDashboard() {
        return [
            'topPlayer'=>self::topPlayer(5),
            'topScorers'=>self::topScorers(5),
            'topKeepers'=>self::topKeepers(5),
            'topAssistants'=>self::topAssistants(5),
            'topDefenders'=>self::topDefenders(5),
            'topHonor'=>self::topHonor(5),
        ];
    }
    public function show($id,Request $request){
        if($id<0){
            $player=$request->user()->player;
            if($player==null){
                return response([
                    'message'=>'this account doesnt belong to a player'
                ],400);
            }
            $id=$player->id;
        }
        $player= Player::where('id',$id)
        ->with('team:id,name,logo')
        ->get();
        if(count($player)==0){
            return response([
                'message'=>'player not found'
            ],404);
        }
        $player=$player[0];
        $count=count($player->yellowCards)
        +count($player->redCards);
        $player->makeHidden(['user_id','team_id','redCards','yellowCards']);
        $player->position();//replace numbers with the name 
        $player->cards=$count;
        return $player;
    }
    public function topScorers($limit=null){
        $players= Player::select(['id','name','goals','team_id'])
        ->where('goals','>',0)
        ->orderByDesc('goals')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit($limit??20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topPlayer($limit=null){
        $players= Player::select(['id','name','score','team_id'])
        ->where('score','>',0)
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit($limit??20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topKeepers($limit=null){
        $players= Player::select(['id','name','saves','team_id'])
        ->wherePosition(Player::GOAL_KEEPER)
        ->where('saves','>',0)
        ->orderByDesc('saves')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit($limit??20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topAssistants($limit=null){
        $players= Player::select(['id','name','assists','team_id'])
        ->where('assists','>',0)
        ->orderByDesc('assists')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit($limit??20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topDefenders($limit=null){
        $players= Player::select(['id','name','defences','team_id'])
        ->where('defences','>',0)
        ->orderByDesc('defences')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit($limit??20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topPredictors($limit=null) {
        $players= Player::select(['id','name','prediction','team_id'])
        ->where('prediction','>',0)
        ->orderByDesc('prediction')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit($limit??20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topHonor($limit=null) {
        $players= Player::select(['id','name','honor','team_id'])
        ->where('honor','>',0)
        ->orderByDesc('honor')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit($limit??20)->get();
        $players->makeHidden('team_id');
        return $players;
    }

}