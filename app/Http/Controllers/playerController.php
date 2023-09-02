<?php
namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class playerController extends Controller
{
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
    public function topScorers(){
        $players= Player::select(['id','name','goals','team_id'])
        ->orderByDesc('goals')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit(20)->get();
        $players->makeHidden('team_id');
        return $players;

    }
    public function topPlayer(){
        $players= Player::select(['id','name','score','team_id'])
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit(20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topKeepers(){
        $players= Player::select(['id','name','saves','team_id'])
        ->wherePosition(Player::GOAL_KEEPER)
        ->orderByDesc('saves')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit(20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topAssistants(){
        $players= Player::select(['id','name','assists','team_id'])
        ->orderByDesc('assists')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit(20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topDefenders(){
        $players= Player::select(['id','name','defences','team_id'])
        ->orderByDesc('defences')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit(20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topPredictors() {
        $players= Player::select(['id','name','prediction','team_id'])
        ->orderByDesc('prediction')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit(20)->get();
        $players->makeHidden('team_id');
        return $players;
    }
    public function topHonor() {
        $players= Player::select(['id','name','honor','team_id'])
        ->orderByDesc('honor')
        ->orderByDesc('score')
        ->with('team:id,name,logo')
        ->limit(20)->get();
        $players->makeHidden('team_id');
        return $players;
    }

}