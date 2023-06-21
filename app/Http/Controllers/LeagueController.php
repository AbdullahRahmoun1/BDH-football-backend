<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Team;
use App\Models\Contest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $this->canProceedTo(config('stage.PART ONE'));
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
    public function part2(){  
        //check if the league is currently in part1 stage
        $this->canProceedTo(config('stage.PART TWO'));
        //now check that there isn't any declared or undeclared matches
        $matchesCount=Contest::where('stage',config('stage.PART ONE'))
        ->where('firstTeamScore','<',0)
        ->where('secondTeamScore','<',0)
        ->count();
        if($matchesCount>0)
        abort(400,"There are ".$matchesCount." matches that need to be played.."
        ."(declare [date(or/and)results]) of these matches.");
        //now we can proceed to part2
        //how many teams will go to part2 from every class?
        $winnerNum=request()->validate([
            'teamsInEachClass'=>['required','numeric','between:1,6']
        ]);
        $winnerNum=$winnerNum['teamsInEachClass'];
        DB::beginTransaction();
        $teamsInPartTwo=0;
        try{
            //get all calsses
            $classes=Team::select('class')
            ->distinct()
            ->get()->pluck('class');
            //now iterate over them and get the winners
            foreach($classes as $class){
                //  FIXME:make sure this is the right order
                $teams=Team::partOne()->where('class',$class)
                ->orderByDesc('diff')
                ->orderByDesc('points')
                ->take($winnerNum)
                ->get();
                foreach($teams as $team){
                    $team->stage=config("stage.PART TWO");
                    $team->save();
                    $teamsInPartTwo++;
                }
            }
        }catch(Exception $e){
            DB::rollBack();
            abort('Something went wrong..error('.$e->getMessage().')..contact developer');
        }
        LeagueController::updateInSettingsFile(array
        ('currentStage'=>config('stage.PART TWO')));
        DB::commit();
        return [
            'message'=>'The league is now in PART TWO stage!, and '.$teamsInPartTwo .
            ' teams proceded to this stage',
            'TeamCount'=>$teamsInPartTwo
        ];
        
    }
    public static function updateInSettingsFile($data1){
        $data=config('leagueSettings');
        foreach($data1 as $key => $value){
            $data[$key]=$value;
        }
        $data=var_export($data,true);
        file_put_contents(base_path() . '\config\leagueSettings.php',"<?php\n return $data ;");
    }
    public static function canProceedTo($stage){
        $current=config('leagueSettings.currentStage');
        if($stage!=$current+1)    
        abort(400,"you can't progress to ".config('stage.'.$stage)." when the league is currently at "
        .config("stage.".$current))
        ;
    }
}
