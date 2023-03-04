<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PSpell\Config;

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
        self::handleTheExcelFile();
        //get all grades
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
                        break;
                    
                    default:
                    $warnings[]="Couldn't make the matches of ( Class $class Grade $grade ) because it has " 
                    .$teamsCount." teams in it";
                }
            }
        }
        return [
            'matchesCount'=>$counter,
            'warnings'=>$warnings,
        ];
    }
    public function handleTheExcelFile()
    {
        //here we will extract the info from the file and insert it to DB
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
}
