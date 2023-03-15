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
        ->with('team:id,name')
        ->limit(15)->get();
        $players->makeHidden('team_id');
        return $players;

    }
    public function topPlayer(){
        $players= Player::select(['id','name','score','team_id'])
        ->orderByDesc('score')
        ->with('team:id,name')
        ->limit(15)->get();
        $players->makeHidden('team_id');
        return $players;
    }
}