<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function post(Request $request){
        $errorMsg='';
        $body=$request->validate([
            'contest_id'=>['required','exists:contests,id'],
            'winner'=>['required','between:0,2'],
            'question1'=>['required','boolean'],
            'question2'=>['required','boolean'],
        ]);
        $user=$request->user();
        //make sure this user is a player
        if($user->player==null){
            $errorMsg='Only players can post predictions';
        }
        //make sure this player didnt post for this contest earlier
        $num=Prediction::where('user_id',$user->id)
        ->where('contest_id',$body['contest_id'])
        ->count();
        if($num!=0){
            $errorMsg='This account already posted a prediction for this contest';
        }
        if(empty($errorMsg)){
            //all is good .. add the prediction
            $body['user_id']=$user->id;
            Prediction::create($body);
            return ['message'=>'success'];
        }else{
            return response(['message'=>$errorMsg],400);
        }
        

    }
}
