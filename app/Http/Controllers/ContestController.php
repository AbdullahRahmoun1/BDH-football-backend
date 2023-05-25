<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Player;
use App\Models\Contest;
use App\Models\RedCard;
use App\Models\YellowCard;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContestController extends Controller
{
    public function finishedMatches()
    {
        $matches = Contest::with(['firstTeam:id,name,logo', 'secondTeam:id,name,logo'])
            ->where('firstTeamScore', '>', -1)
            ->orderBy('updated_at')
            ->get();
        $matches->makeHidden([
            'firstTeam_id', 'secondTeam_id', 'period'
        ]);
        return $matches;
    }
    public function unFinishedMatches()
    {
        $matches = Contest::with(['firstTeam:id,name,logo', 'secondTeam:id,name,logo'])
            ->where('secondTeamScore','<=', -1)
            ->where('firstTeamScore','<=', -1)
            ->whereNotNull('date')
            ->orderBy('date')
            ->get();
        $matches->makeHidden([
            'firstTeam_id',
            'secondTeam_id',
            'firstTeamScore',
            'secondTeamScore',
            'period',
        ]);
        return $matches;
    }
    public function unDeclaredMatches()
    {
        $data = request()->validate([
            'grade' => 'required',
            'class' => 'required'
        ]);
        //get all the matches where the first team is pointing at a team that is from the grade x
        //and class y.. bcs in level 1 always matches are between two teams from the same class
        //we can ignore the secondteam relation 
        return Contest::with([
            'firstTeam:id,name,logo',
            'secondTeam:id,name,logo'
            ])->whereNull('date')
            ->where('firstTeamScore', '<', 0)
            ->where('secondTeamScore', '<', 0)
            ->whereHas(
                'firstTeam',
                fn ($q) =>
                $q->where('grade', $data['grade'])
                    ->where('class', $data['class'])
            )
            ->orderBy('period')
            ->orderByDesc('league')
            ->get();
    }
    public function declareMatch(Contest $contest,Request $request){
        $data=$request->validate([
            'date'=>'required'
        ]);
        if($contest->firstTeamScore>=0 
        || $contest->secondTeamScore>=0){
            return response()->json([
                'message'=>"you can't edit the date of a finished match"
            ],400); 
        }
        $contest->date=$data['date'];
        $contest->save();
        return ['message'=>'success'];
    }
    public function declareMatchResults(Request $request,Contest $match){
        $this->determinState($match);
        if($match->state!=config('consts.declared'))
        abort('422','match is either not declared yet or finished');
        $request->validate([
            'firstTeamGoals'=>['required','array'],
            'secondTeamGoals'=>['required','array'],
            'yellowCards'=>['required','array'],
            'redCards'=>['required','array'],
            'honor'=>['required','array'],
        ]);
        DB::beginTransaction();
        try{
            $match->firstTeamScore=count($request->firstTeamGoals);
        foreach($request->firstTeamGoals as $playerId){
            Goal::create([
                'player_id'=>$playerId,
                'contest_id'=>$match->id,
                'team_id'=>$match->firstTeam_id
            ]);
        }
        $match->secondTeamScore=count($request->secondTeamGoals);
        //TODO: add affect of (goals,yellow cards) on player if needed
        foreach($request->secondTeamGoals as $playerId){
            if($playerId!=null &&$playerId>0)
            Goal::create([
                'player_id'=>$playerId,
                'contest_id'=>$match->id,
                'team_id'=>$match->secondTeam_id
            ]);
        }
        foreach($request->yellowCards as $playerId){
            if($playerId!=null &&$playerId>0)
            YellowCard::create([
                'contest_id'=>$match->id,
                'player_id'=>$playerId
            ]);
        }
        foreach($request->redCards as $playerId){
            if($playerId!=null &&$playerId>0)
            RedCard::create([
                'contest_id'=>$match->id,
                'player_id'=>$playerId
            ]);
        }
        Player::whereIn('id',$request->honor)
        ->increment('honor',config('consts.honor'));
        }catch(Exception $e){
            DB::rollBack();
            abort(400,'something went wrong'.$e->getMessage());
            Log::log(1,$e->getMessage());
            
        }
        DB::commit();
        unset($match->state);
        $match->save();
        return [
            'message'=>'success'
        ];
    }
    public function viewMatchinfo(Request $request ,Contest $match){
        $this->determinState($match);
        $match->firstTeam=teamController::show($match->firstTeam_id,['id','name','logo']);
        $match->secondTeam=teamController::show($match->secondTeam_id,['id','name','logo']);
        if($match->state==config('consts.finished')){
            $match->load(['goals','yellowCards','redCards']);
        }
        
        return $match;
    }
    private function determinState($match)
    {
        if($match->date){
            if($match->firstTeamScore>=0 &&$match->secondTeamScore>=0)
                $state=config('consts.finished');
            elseif($match->firstTeamScore>=0 ||$match->secondTeamScore>=0){
                $state=config('consts.finished');
                //TODO: inform the admin that this error happened
            }else{
                $state=config('consts.declared');
            }
        }else {
            $state=config('consts.undeclared');
        }
        $match->state=$state;
    }
}
