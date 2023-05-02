<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    public function finishedMatches()
    {
        $matches = Contest::with(['firstTeam:id,name,logo', 'secondTeam:id,name,logo'])
            ->where('firstTeamScore', '>', -1)
            ->orderByDesc('updated_at')
            ->get();
        $matches->makeHidden([
            'firstTeam_id', 'secondTeam_id', 'place', 'period', 'league', 'stage'
        ]);
        return $matches;
    }
    public function unFinishedMatches()
    {
        $matches = Contest::with(['firstTeam:id,name,logo', 'secondTeam:id,name,logo'])
            ->where('firstTeamScore', '<=', -1)
            ->whereNotNull('date')
            ->orderByDesc('date')
            ->get();
        $matches->makeHidden([
            'firstTeam_id', 'secondTeam_id', 'firstTeamScore', 'secondTeamScore', 'period', 'league', 'stage'
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
        return Contest::whereNull('date')
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
}
