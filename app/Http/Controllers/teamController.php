<?php
namespace App\Http\Controllers;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
class teamController extends Controller
{
    public function show($id){
        $team=Team::with('players:id,team_id,name,position')
        ->findOrFail($id);
        $players=$team->players;
        $players->makeHidden(['team_id','position']);
        $captain=$players->firstWhere('position',Player::CAPTAIN);
        $goalKeeper=$players->firstWhere('position',Player::GOAL_KEEPER);
        $players=$players->whereNotIn('id',[$captain->id,$goalKeeper->id])->all();
        $team->attackers=array_values($players);
        $team->captain=$captain;
        $team->goalKeeper=$goalKeeper;
        $team->makeHidden('players');
        return $team;
    }
    public function gradesAndClassesSuggestions(){
        $suggesions=[];
        $grades=Team::select('grade')
        ->distinct()
        ->orderBy("grade")
        ->get()->pluck("grade");
        foreach ($grades as  $grade) {
            $classes=Team::select('class')
            ->where('grade',$grade)
            ->distinct()
            ->orderBy("class")
            ->get()->pluck("class");
            foreach($classes as $class){
                $suggesions[]="Grade ".$grade." Class ".$class;
            }
        }
        return $suggesions;
    }
    public function part1Teams(){
        $slugs= $this->gradesAndClassesSuggestions();
        $result=[];
        foreach($slugs as $slug){
            $string = explode(" ", $slug);
            //grade x class y   grade index=>1  class=>3
            $teams=Team::select([
                'name',
                'logo',
                'wins',
                'ties',
                'losses',
                'points'])
                ->where('grade',$string[1])
                ->where('class',$string[3])
                ->where('stage',Config::get('constants.level1'))
                ->orderByDesc('points')
                ->get();
            $result[$slug]=$teams;
        }
        return $result;
    }
    public function part2Teams(){
        return Team::select('id','name','logo','points')
        ->where('stage',Config::get('constants.level2'))
        ->orderByDesc('points')
        ->get();
    }
    public function uploadTeamLogo(Request $request,$id){
        $request->validate([
            'image'=>['file','required','max:1024']
        ],[
            'image.max'=>'The image must not be greater than 1MB.'
        ]);    
        $team=Team::findOrFail($id);
        $image=$request->file('image');
        $imageName=$team->name."($team->id)";//.$ext";
        // get all the files with the same name..then delete them
        //glob gets all the fiels that their path matches the patter provided
        array_map('unlink'
        ,glob("../storage/app/public/logos/$imageName.*"));
        //add the extension to name
        $imageName.=".{$image->extension()}";
        $team->logo=$imageName;
        $team->save();
        $image
        ->storeAs("public/logos",$imageName);
        return ['message'=>'successe'];
    }

}
