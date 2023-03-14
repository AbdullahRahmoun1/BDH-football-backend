<?php
namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class playerController extends Controller
{
    public function show($id,Request $request)
    {
        if($id<0){
            $user=$request->user();
            //must change to relation later
            // return $user->userName;
            $player=$user->player;
            //end
            if($player==null){
                return response([
                    'message'=>'this account doesnt belong to a player'
                ],400);
            }
            return $player;
        }
        return Player::findOrFail($id);
    }
}