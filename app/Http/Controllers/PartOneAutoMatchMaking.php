<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use PSpell\Config;
use App\Models\Team;
use App\Models\Contest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartOneAutoMatchMaking extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //check if the league is in part one
        if(config('leagueSettings.currentStage')!=config('stage.PART ONE'))
        abort(422,'The league has to be in stage ( Part One ) to do this action..League is now in '
        .config('stage.'.config('leagueSettings.currentStage')));
        //check if the autoMatchMaking haven't been called before
        if(config('leagueSettings.autoMatchMakingDone',false))
        abort(422,'AutoMatchMaking is already done..you can\'t do it again!');
        //is there any teams?

        if(Team::count()==0){
            abort(422,"There is 0 teams.. please insert teams first!");
        }
        //get all grades
        DB::beginTransaction();
        try{
            $warnings=[];
            $counter=0;
            $grades=Team::select('grade')->distinct()->get()->pluck('grade');
            foreach ($grades as  $grade) {
                $classes=Team::select('class')
                ->where('grade',$grade)
                ->distinct()
                ->get()->pluck('class');
                foreach ($classes as  $class) {
                    $teams=Team::where('grade',$grade)
                    ->where('class',$class)
                    ->get();
                    $teamsCount=$teams->count();
                    switch($teamsCount){
                        case 5:
                            $contests=$this->fiveTeamsSchema($teams);
                            $counter+=count($contests);
                            Contest::insert($contests);
                            break;
                        case 6:        
                            $contests=$this->sixTeamsSchema($teams);
                            $counter+=count($contests);
                            Contest::insert($contests);
                            break;         
                        default:
                        $warnings[]="Couldn't make the matches of ( Class $class Grade $grade ) because it has " 
                        .$teamsCount." teams in it..fix the issue then try again";
                    }
                }
            }
        }catch(Exception $e){
            $warnings[]="Error(".$e->getMessage()." at line "
            .$e->getLine()."). Contact developer";
        }
        if(count($warnings)!=0){
            DB::rollback();
            return response()->json([
                'message'=>'found '.count($warnings).' errors or warnings',
                'warnings/errors'=>$warnings
            ]);
        }
        DB::commit();
        LeagueController::updateInSettingsFile(['autoMatchMakingDone'=>true]);
        return [
            'message'=>'Success!, '.$counter.' Matches created',
            'matchesCount'=>$counter,
        ];
    }
    public function fiveTeamsSchema($teams){
        $yard=Contest::YARD;
        $ground=Contest::PLAY_GROUND;
        return [
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>1,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>1,
                'league'=>true,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>1,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[3]->id,
                'secondTeam_id'=>$teams[4]->id,
                'period'=>1,
                'league'=>false,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[4]->id,
                'period'=>2,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[4]->id,
                'period'=>2,
                'league'=>true,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>2,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>2,
                'league'=>false,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[2]->id,
                'period'=>3,
                'league'=>true,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>3,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>3,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>3,
                'league'=>false,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>4,
                'league'=>true,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>4,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>4,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[4]->id,
                'period'=>4,
                'league'=>false,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[3]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>5,
                'league'=>true,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>5,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[4]->id,
                'period'=>5,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>5,
                'league'=>false,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ]
        ];
    }
    public function sixTeamsSchema($teams){
        $yard=Contest::YARD;
        $ground=Contest::PLAY_GROUND;
        return [
            //league games 
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>1,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>1,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[4]->id,
                'period'=>2,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[1]->id,
                'secondTeam_id'=>$teams[5]->id,
                'period'=>2,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[2]->id,
                'period'=>3,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[5]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>3,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[5]->id,
                'period'=>4,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>4,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>5,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[5]->id,
                'secondTeam_id'=>$teams[0]->id,
                'period'=>5,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[1]->id,
                'secondTeam_id'=>$teams[4]->id,
                'period'=>6,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[5]->id,
                'secondTeam_id'=>$teams[2]->id,
                'period'=>6,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[1]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>7,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[0]->id,
                'period'=>7,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[3]->id,
                'secondTeam_id'=>$teams[4]->id,
                'period'=>8,
                'league'=>true,
                'place'=>$yard,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            //not league games----------------------------------------------------------------
            //---------------------------------------------------------------------------------
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[5]->id,
                'period'=>1,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[5]->id,
                'period'=>1,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>2,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[2]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>2,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>3,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[1]->id,
                'period'=>3,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[1]->id,
                'secondTeam_id'=>$teams[2]->id,
                'period'=>4,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[1]->id,
                'secondTeam_id'=>$teams[2]->id,
                'period'=>4,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>5,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>5,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>6,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[0]->id,
                'secondTeam_id'=>$teams[3]->id,
                'period'=>6,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[5]->id,
                'period'=>7,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
            [
                'firstTeam_id'=>$teams[4]->id,
                'secondTeam_id'=>$teams[5]->id,
                'period'=>7,
                'league'=>false,
                'place'=>$ground,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ],
        ];
    }
}
