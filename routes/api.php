<?php

use App\Http\Controllers\ContestController;
use App\Http\Controllers\HandleExcelInput;
use App\Http\Controllers\LeagueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\playerController;
use App\Http\Controllers\PartOneAutoMatchMaking;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\teamController;
use App\Models\Team;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\TextUI\XmlConfiguration\Logging\TeamCity;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('adminThing',function(){
    Artisan::call('migrate:fresh --seed');
    Artisan::call('storage:link');
});

Route::post('signup',[userController::class,'signup']);
Route::post('login',[userController::class,'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/self',[userController::class,'self']);
    Route::get('/logout',[userController::class,'logout']);
    Route::get('player/{id}',[playerController::class,'show']);
    Route::post('/prediction',[PredictionController::class,'post']);
    Route::get('topScorers',[playerController::class,'topScorers']);
    Route::get('topKeepers',[playerController::class,'topKeepers']);
    Route::get('topDefenders',[playerController::class,'topDefenders']);
    Route::get('topPlayer',[playerController::class,'topPlayer']);
    Route::get('team/{team}',[teamController::class,'show']);
    Route::post('team/{id}',[teamController::class,'uploadTeamLogo']);
    Route::get('part2/teams',[teamController::class,'part2Teams']);
    Route::get('part1/teamsSearchSuggestions',[teamController::class,'gradesAndClassesSuggestions']);
    Route::get('part1/teams',[teamController::class,'part1Teams']);
    Route::get('part1/teamsSortedByPoints',[teamController::class,'part1TeamsSortedByBest']);
    Route::post('part1/declareMatch/{contest}',[ContestController::class,'declareMatch']);
    Route::get('finishedMatches',[ContestController::class,'finishedMatches']);
    Route::get('unFinishedMatches',[ContestController::class,'unFinishedMatches']);
    Route::get('viewMatchInfo/{match}', [ContestController::class,'viewMatchInfo']);
    Route::get('league', [LeagueController::class,'view']);

    //admin---------------------------
    Route::middleware('ability:'.config('consts.admin'))
    ->group(function(){
        Route::post('excelInput',HandleExcelInput::class);
        Route::post('matchMaking',PartOneAutoMatchMaking::class);
        Route::post('declareMatchResults/{match}',[ContestController::class,'declareMatchResults']);
        Route::post('createAdmin',[userController::class,'createAdmin']);
        Route::post('createGuest',[userController::class,'createGuest']);
        Route::post('newMatch',[ContestController::class,'addNewContest']);
        Route::delete('deleteMatch/{contest}',[ContestController::class,'deleteContest']);    
        Route::get('part1/unDeclaredMatches',[ContestController::class,'unDeclaredMatches']);
        Route::post('admin/advanceToPartOne',[LeagueController::class,'part1']);
        Route::post('admin/advanceToPartTwo',[LeagueController::class,'part2']);
        Route::delete('admin/restartLeague',[LeagueController::class,'restartLeague']);
        Route::post('admin/declareWinners',[LeagueController::class,'declareWinners']);
        Route::post('admin/uploadPart2Tree',[LeagueController::class,'uploadPart2Tree']);

    });
    
});


