<?php

use App\Http\Controllers\ContestController;
use App\Http\Controllers\HandleExcelInput;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\playerController;
use App\Http\Controllers\PartOneAutoMatchMaking;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\teamController;
use Illuminate\Support\Facades\Artisan;
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
    //auth
    Route::get('/logout',[userController::class,'logout']);
    //view self
    Route::get('/self',[userController::class,'self']);
    //view players
    Route::get('player/{id}',[playerController::class,'show']);
    Route::get('topScorers',[playerController::class,'topScorers']);
    Route::get('topKeepers',[playerController::class,'topKeepers']);
    Route::get('topAssistants',[playerController::class,'topAssistants']);
    Route::get('topDefenders',[playerController::class,'topDefenders']);
    Route::get('topPlayer',[playerController::class,'topPlayer']);
    Route::get('topPredictors',[playerController::class,'topPredictors']);
    Route::get('topHonor',[playerController::class,'topHonor']);
    //view teams
    Route::get('team/{team}',[teamController::class,'show']);
    Route::post('team/{id}',[teamController::class,'uploadTeamLogo']);
    Route::get('part2/teams',[teamController::class,'part2Teams']);
    Route::get('part1/teamsSearchSuggestions',[teamController::class,'gradesAndClassesSuggestions']);
    Route::get('part1/teams',[teamController::class,'part1Teams']);
    Route::get('part1/teamsSortedByPoints',[teamController::class,'part1TeamsSortedByBest']);
    //view matches
    Route::get('finishedMatches',[ContestController::class,'finishedMatches']);
    Route::get('unFinishedMatches',[ContestController::class,'unFinishedMatches']);
    Route::get('viewMatchInfo/{match}', [ContestController::class,'viewMatchInfo']);
    Route::get('league', [LeagueController::class,'view']);
    //post player prediction
    Route::post('/prediction',[PredictionController::class,'post']);

    //admin---------------------------
    Route::middleware('ability:'.config('consts.admin'))
    ->group(function(){
        //accounts
        Route::post('createAdmin',[userController::class,'createAdmin']);
        Route::post('createGuest',[userController::class,'createGuest']);
        //league settings
        Route::post('admin/advanceToPartOne',[LeagueController::class,'part1']);
        Route::post('admin/advanceToPartTwo',[LeagueController::class,'part2']);
        Route::delete('admin/restartLeague',[LeagueController::class,'restartLeague']);
        Route::post('admin/declareWinners',[LeagueController::class,'declareWinners']);
        //easy players insertion
        Route::post('excelInput',HandleExcelInput::class);
        Route::post('matchMaking',PartOneAutoMatchMaking::class);
        //add+edit match
        Route::post('newMatch',[ContestController::class,'addNewContest']);
        Route::post('part1/declareMatch/{contest}',[ContestController::class,'declareMatch']);
        Route::post('declareMatchResults/{match}',[ContestController::class,'declareMatchResults']);
        Route::delete('deleteMatch/{contest}',[ContestController::class,'deleteContest']);    
        //view undeclared matches
        Route::get('part1/unDeclaredMatches',[ContestController::class,'unDeclaredMatches']);
        //upload part 2 tree        
        Route::post('admin/uploadPart2Tree',[LeagueController::class,'uploadPart2Tree']);
    });
    
});


