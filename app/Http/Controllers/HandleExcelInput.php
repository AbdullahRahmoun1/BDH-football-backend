<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\User;
use App\Models\Player;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rules\File as RulesFile;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class HandleExcelInput extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public $row=0;
    public function __invoke(Request $request){
        //check if the league is in part one
        if(config('leagueSettings.currentStage')!=config('stage.PART ONE'))
        abort(422,'you can add players only when the league is in PART ONE stage');
        //validate the input
        $request->validate([
            'excel'=>['required',RulesFile::types(['xlsx']),'max:10000']
        ]);
        //get the file
        $file=$request->file('excel');
        //make sure its valid
        if(!$file->isValid()){
            return response([
                'message'=>'The excel file is invalid'
            ],422);
        }
        //initialize the reader
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->setShouldPreserveEmptyRows(true);
        $reader->open($file->getRealPath());
        //initialize some helpers
        $rowI=1;
        $errors=[];
        $accounts=[];
        //begin the DB transaction because so many things could go wrong
        DB::beginTransaction();
        //iterate over all sheets (there will be one only)
        //then over all rows of a sheet
        foreach ($reader->getSheetIterator() as $sheet) {
            if(count($errors)!=0)break;
            foreach ($sheet->getRowIterator() as $row) {
                $this->row=$rowI;
                $cells=$row->getCells();
                if($rowI!=1 && count($cells)!=0){
                    //make sure cells are all filled
                    if(self::rowIsGood($cells)){
                    $team=self::insertOrDuplicateError(fn()=>
                        Team::create(self::extractTeamInfo($cells))
                    ,'team already exists. row = '.$rowI,$errors);
                    if(!is_null($team)){
                        //if the team is created successfully..then we can create the players and accounts
                        $teamId=$team->id;
                        $dbAccounts=self::extractAccountsInfo($cells,$accounts);
                        $dbIds=array_map(fn($account)
                        =>self::insertOrDuplicateError(fn()=>
                            User::insertGetId($account)
                            ,'Account already exists. row = '.$rowI,$errors,true)
                        ,$dbAccounts);
                        //accounts are created !!
                        //now lets create the player records if no errors found yet..
                            if(count($errors)==0){
                                self::insertOrDuplicateError(fn()=>
                                Player::insert(self::extractPlayersInfo($cells,$teamId,$dbIds))
                                ,'Player already exists. row = '.$rowI,$errors,true);
                            }
                        
                    }
                    //now create the players records and create a password for them
                    }else {
                        //if not good then their is an error (empty fields mainly)
                        if(count($cells)!=0)
                        $errors[]='check for empty fields in row '.$rowI;
                    }
                    //now create teams
                }                
                $rowI++;
            }
        }
        $reader->close();
        //now lets make sure that every class has 5 or 6 teams
        $this->checkIfAllClassesHasEnoughTeams($errors);
        if(count($errors)==0){
            //no errors? => thank god.. commit the transaction
            DB::commit();
            return response()->file(self::arrayToExcel($accounts));
        }else{
            //errors -_- => i hate my life.. rollback the transaction
            DB::rollback();
            return response([
                'message'=>'found '.count($errors).' errors',
                'errors'=>$errors
            ],400);
        }
    }
    private function checkIfAllClassesHasEnoughTeams(&$errors){
        $grades=Team::select('grade')
        ->distinct()
        ->get()
        ->pluck("grade");
        foreach ($grades as  $grade) {
            $classes=Team::select('class')
            ->where('grade',$grade)
            ->distinct()
            ->get()
            ->pluck("class");
            foreach($classes as $class){
                $count=Team::where('grade',$grade)
                ->where('class',$class)
                ->where('stage',config('stage.PART ONE'))
                ->count();
                if($count!=5 && $count!=6){
                    $errors[]="grade $grade class $class doesn't have enough teams. it has $count teams..should be 5 or 6";
                }
            }
        }  
    }
    private function rowIsGood(&$cells){
        //remove all empty cells
        foreach($cells as $key=>$cell){
            if($cell->getValue()=='')
                unset($cells[$key]);
        }
        if(count($cells)<=0)
        return false;
        //check that important fields are filled
        $i=4;
        do{
            if(!isset($cells[$i])){
                return false;
            }
        }while(--$i>=0);
        return true;
    }
    private function extractTeamInfo($cells){
        $grade=$cells[0]->getValue();
        $grade=strtoupper(trim($grade));
        $class=$cells[1]->getValue();
        $class=strtoupper(trim($class));
        $teamName=$cells[2]->getValue();
        return [
            'grade'=>$grade,
            'class'=>$class,
            'name'=>$teamName,
        ];
    }
    private function extractPlayersInfo($cells,$team_id,$dbIds){
        $players=[];
        for($i=3 ;$i<count($cells);$i++){
            $players[]=[
                'name'=>$cells[$i]->getValue(),
                'position'=>$i==3?Player::CAPTAIN:($i==4?Player::GOAL_KEEPER:Player::NORMAL),
                'team_id'=>$team_id,
                'user_id'=>$dbIds[$i-3],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ];
        }
        return $players;
    }
    private function extractAccountsInfo($cells,&$accounts){
        #remember to keep track of the passwords
        $dbAccounts=[];
        for($i=3 ;$i<count($cells);$i++){
            $pass=Str::random(7);
            $dbAccounts[]=[
                'userName'=>$cells[$i]->getValue(),
                'password'=>bcrypt($pass),
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ];
            $accounts[]=[
                'grade'=>$cells[0]->getValue(),
                'class'=>$cells[1]->getValue(),
                'userName'=>$cells[$i]->getValue(),
                'password'=>$pass,
            ];
        }
        return $dbAccounts;
    }
    private function insertOrDuplicateError($function,$msg,&$errors,$withDetails=false){
        $result=null;
        try{
            $result=$function();
        }catch(QueryException $e){
            $errorCode=$e->errorInfo[1];
            if($errorCode==1062){
                // we have a duplicate entry problem
                abort(400,'fetal error ( '.$msg.' )'.($withDetails?'___ details: '.$e->errorInfo[2]:''));
            }else { 
                $errors[]=$e->errorInfo[2];
            }
        }
        return $result;
    }
    private function arrayToExcel($array)
    {
        //create a file..get its path...initialize the writer
        $file = fopen(base_path().'\\storage\\app\\BaderAldean_Accounts.xlsx','w');
        $path = stream_get_meta_data($file)['uri']; 
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($path);
        //now add the rows
        $rows=array_map(fn($account)
        =>WriterEntityFactory::createRowFromArray($account),
        $array);
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Grade','Class','UserName','Password']));
        $writer->addRows($rows);
        //close the file and writer
        $writer->close();
        fclose($file);
        return $path;
    }
}
