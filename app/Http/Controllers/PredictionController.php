<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;

class PredictionController extends Controller
{
    public function post(Request $request){
        $errorMsg='';
        $body=$request->validate([
            'contest_id'=>['required','exists:contests,id'],
            'winner'=>['required','between:0,2'],
            'question1'=>['required','boolean'],
            'question2'=>['required','boolean'],
            'double'=>['required','boolean']
        ]);
        //make sure that this match is not finished yet
        $match=Contest::find($body['contest_id']);
        Contest::determinState($match);
        if($match->state!=config('consts.declared'))
        abort(400,'This match is finished or undeclared yet!');
        $user=$request->user();
        //FIXME: REMOVE THE IF(IS A PLAYER) IF YOU WANT ANY BODY TO PREDICT
        //make sure this user is a player
        // if($user->player==null){
        //     abort(400,'Only players can post predictions.');
        // }
        //is voting closed for this prediction?
        if(!self::isVotingAvailable($match)){
            abort(400,'Voting ends at 07:00 AM on match day.');
        }
        try{
            $body['user_id']=$user->id;
            Prediction::create($body);
            return ['message'=>'success'];
        }catch(QueryException $e){
            $errorCode=$e->errorInfo[1];
            if($errorCode==1062){
                $errorMsg='This account already posted a'.
                ' prediction for this contest';
            }else {
                $errorMsg=$e->getMessage();
            }
            abort(400,$errorMsg);
        }
    }
    public static function applyMatchResultsAffect($match){
        //must call (set setPredictionAnswers in contest controller to set the match answers before calling this route)
        $predecs=$match->predictions;
        foreach($predecs as $prediction){
            $player=$prediction->user->player;
            if($player==null)continue;
            $x=$prediction->double?2:1;
            $right=0;
            $wrong=0;
            if($prediction->winner==$match->answer_winner)
            $right++; else $wrong++;
            if($match->answerOfFirstQuestion==$prediction->question1)
            $right++; else $wrong++;
            if($match->answerOfFirstQuestion==$prediction->question1)
            $right++; else $wrong++;
            $total=$right*config('consts.rightPrediction')
            - $wrong*config("consts.wrongPrediction");
            $player->increment('prediction',$total*$x);
        }
    }
    public static function isVotingAvailable($match){
        $endDateTime=Carbon::parse($match->date)->setTime(7, 0, 0);
        $currentTime=now();
        return $currentTime<$endDateTime;
    }
}

