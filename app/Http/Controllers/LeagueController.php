<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;


class LeagueController extends Controller
{
    public function view() {
        $data=config('leagueSettings');
        if(request()->user()->owner_type!=config('consts.admin')){
            unset($data['prediction question 1']);
            unset($data['prediction question 2']);
        }
        $data['currentStage']=config('stage.'.$data['currentStage']);
        return $data;
    }
    public function part1(){
        $current=config('leagueSettings.currentStage');
        if($current!=config('stage.VOID'))
        abort(400,"you cant progress to part one when the league is currently at "
        .config("stage.".$current));
        $data1=request()->validate([
            'title'=>['required','string','between:5,100'],
            'startDate'=>['required','date','after:'.now()->subDay()],
            'predictionQuestion1'=>['required','string','between:10,200'],
            'predictionQuestion2'=>['required','string','between:10,200'],
        ]);
        $data1['currentStage']=config('stage.PART ONE');
        $this->updateInSettingsFile($data1);
        return response()->json(['message'=>'part one started successfully!']);
    }   
    public static function updateInSettingsFile($data1){
        $data=config('leagueSettings');
        foreach($data1 as $key => $value){
            $data[$key]=$value;
        }
        $data=var_export($data,true);
        file_put_contents(base_path() . '\config\leagueSettings.php',"<?php\n return $data ;");
    }
    public static function isleagueInStage($stage){
        return config('leagueSettings.currentStage')==$stage;
    }
}
