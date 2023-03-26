<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    public function finishedMatches()
    {
        $matches=Contest::with(['firstTeam:id,name,logo','secondTeam:id,name,logo'])
        ->where('firstTeamScore','>',-1)
        ->orderByDesc('updated_at')
        ->get();
        $matches->makeHidden([
            'firstTeam_id'
            ,'secondTeam_id'
            ,'place'
            ,'period'
            ,'league'
            ,'stage'
        ]);
        return $matches;
    }
    public function unFinishedMatches()
    {
        $matches=Contest::with(['firstTeam:id,name,logo','secondTeam:id,name,logo'])
        ->where('firstTeamScore','<=',-1)
        ->whereNotNull('date')
        ->orderByDesc('date')
        ->get();
        $matches->makeHidden([
            'firstTeam_id'
            ,'secondTeam_id'
            ,'firstTeamScore'
            ,'secondTeamScore'
            ,'period'
            ,'league'
            ,'stage'
        ]);
        return $matches;
    }
}
