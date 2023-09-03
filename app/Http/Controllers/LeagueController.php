<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Goal;
use App\Models\Team;
use App\Models\User;
use App\Models\Player;
use App\Models\Contest;
use App\Models\RedCard;
use App\Models\Prediction;
use App\Models\YellowCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

class LeagueController extends Controller
{

    public function view() {
        $data=config('leagueSettings');
        $remove = [];
        $current=config('leagueSettings.currentStage');
        $part1=config('stage.PART ONE');
        $part2=config('stage.PART TWO');
        //not an admin?no need forit
        if(request()->user()->owner_type
        !=config('consts.admin')){
            $remove[]='autoMatchMakingDone';
        }
        if($current<=$part1){
            //partOne doesn't need any of thos
            array_push($remove,...[
                '7&8 Winner',
                '9 Winner',
                'treeUri(7&8)',
                'treeUri(9)'
            ]);
        }
        else if($current<=$part2){
            //partTwo doesn't need those
            array_push($remove,...[
                '7&8 Winner',
                '9 Winner',
            ]);
        }
        $data = array_diff_key($data, array_flip($remove));
        $data['currentStage']=config('stage.'.$data['currentStage']);
        return $data;
    }
    public static function restartLeague() {
        try{
            User::where('owner_type',config('consts.student'))
            ->delete();
            PersonalAccessToken::
            where('abilities','like','%'.config('consts.student').'%')
            ->delete();
            Schema::disableForeignKeyConstraints();
            Contest::truncate();
            Goal::truncate();
            YellowCard::truncate();
            RedCard::truncate();
            Team::truncate();
            Player::truncate();
            Prediction::truncate();
            Schema::enableForeignKeyConstraints();
            LeagueController::updateInSettingsFile([
                'currentStage' => 0,
                'autoMatchMakingDone' => false
            ]);
        }catch(Exception $e){
            abort(400,'Restarting faild :( , Cause: '
            .$e->getMessage());
        }
        return [
            'message'=>'League restarted successfully :)'
        ];
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
            'teamsInEachClass(7&8)'=>['required','numeric','between:1,6'],
            'teamsInEachClass(9)'=>['required','numeric','between:1,6']
        ]);
        $winnerNum8=$winnerNum['teamsInEachClass(7&8)'];
        $winnerNum9=$winnerNum['teamsInEachClass(9)'];
        
        DB::beginTransaction();
        $teamsInPartTwo8=0;
        $teamsInPartTwo9=0;
        try{
            //get all calsses
            $classes=Team::select('class','grade')
            ->distinct()
            ->get();
            //now iterate over them and get the winners
            foreach($classes as $class){
                $l1=$class->grade!=9;
                // return $class->grade."   shslhs    ".($l1?$winnerNum8:$winnerNum9);
                $teams=Team::partOne()->where('class',$class->class)
                ->where('grade',$class->grade)
                ->orderByDesc('diff')
                ->orderByDesc('points')
                ->take($l1?$winnerNum8:$winnerNum9)
                ->get();
                foreach($teams as $team){
                    $team->stage=config("stage.PART TWO");
                    $team->save();
                    $teamsInPartTwo8+=$l1?1:0;
                    $teamsInPartTwo9+=$l1?0:1;
                }
            }
        }catch(Exception $e){
            DB::rollBack();
            abort(400,'Something went wrong..error('.$e->getMessage().')..contact developer');
        }
        LeagueController::updateInSettingsFile(array
        ('currentStage'=>config('stage.PART TWO')));
        DB::commit();
        return [
            'message'=>'Sucess!!',
            'League(7&8)'=>$teamsInPartTwo8.' teams advanced to PART_TWO',
            'League(9)'=>$teamsInPartTwo9.' teams advanced to PART_TWO'
        ];
    }
    public function declareWinners(){
        $this->canProceedTo(config('stage.END OF LEAGUE'));
        $data=request()->validate([
            'Winner_id(7&8)'=>['required','numeric','exists:teams,id'],
            'Winner_id(9)'=>['required','numeric','exists:teams,id'],
        ]);
        $l8Winner=Team::find($data['Winner_id(7&8)']);
        $l9Winner=Team::find($data['Winner_id(9)']);
    //check l8 winner for logical errors
        //in right stage?
        if($l8Winner->getRawOriginal('stage')
        !=config('leagueSettings.currentStage'))
        abort(422,"Error(7&8 winner)..This team didn't reach part two!");
        //is it already disqualified?
        if($l8Winner->disqualified)
        abort(422,"Error(7&8 winner)..This team is disqualified!");
        //is it in the right league?
        if($l8Winner->grade==9)
        abort(422,'Error(7&8 winner)..This team is in (9 League)');
    //check l9 winner for logical errors
        //in right stage?
        if($l9Winner->getRawOriginal('stage')
        !=config('leagueSettings.currentStage'))
        abort(422,"Error(9 winner)..This team didn't reach part two!");
        //is it already disqualified?
        if($l9Winner->disqualified)
        abort(422,"Error(9 winner)..This team is disqualified!");
        //is it in the right league?
        if($l9Winner->grade!=9)
        abort(422,'Error(9 winner)..This team is in (7&8 League)');
    //all good...then we proceed
        DB::beginTransaction();
        try{
            $l8Winner->stage=config('stage.END OF LEAGUE');
            $l9Winner->stage=config('stage.END OF LEAGUE');
            $l8Winner->points+=config('consts.leagueWin');
            $l9Winner->points+=config('consts.leagueWin');
            $l8Winner->save();
            $l9Winner->save();
            //now update league settings 
            $new=[
                'currentStage'=>config('stage.END OF LEAGUE'),
                'endDate'=>now(),
                '7&8 Winner'=>$l8Winner->id,
                '9 Winner'=>$l9Winner->id,
            ];
            $this->updateInSettingsFile($new);
        }catch(Exception $e){
            abort(400,$e->getMessage());
            DB::rollBack();
        }
        DB::commit();
        //all done
        return [
            'message'=>'Success!'
        ];
    }
    public function uploadPart2Tree(){
     //validate the input
        $data=request()->validate([
            'image'=>['file','required','max:3072'],
            'league(9)'=>['required','boolean']
        ],[
            'image.max'=>'The image must not be greater than 3MB.'
        ]);    
        $image=request()->file('image');
        $imageName=$data['league(9)']?'(9)':'(7&8)';
     // delete previous image
        //glob gets all the fiels that their path matches the patter provided
        array_map('unlink'
        ,glob("../storage/app/public/leagueTree/$imageName.*"));
     // put it in storage
        //add the extension to name
        $imageFullName="$imageName.{$image->extension()}";
        $image
        ->storeAs("public/leagueTree",$imageFullName);
        $uri="storage/leagueTree/$imageFullName";
        $this->updateInSettingsFile([
            "treeUri$imageName"=>$uri
        ]);
        return [
            'message'=>'successe',
            'newUri'=>$uri
        ];
    }
    public static function updateInSettingsFile($data1){
        $data=config('leagueSettings');
        foreach($data1 as $key => $value){
            $data[$key]=$value;
        }
        $data=var_export($data,true);
        abort(200,config_path());
        unlink(config_path() . '\leagueSettings.php');
        file_put_contents(config_path() . '\leagueSettings.php',"<?php\n return $data ;");
    }
    public static function canProceedTo($stage){
        $current=config('leagueSettings.currentStage');
        if($stage!=$current+1)    
        abort(400,"you can't progress to ".config('stage.'.$stage)." when the league is currently at "
        .config("stage.".$current))
        ;
    }
}
