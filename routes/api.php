<?php

use App\Http\Controllers\ContestController;
use App\Http\Controllers\HandleExcelInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\playerController;
use App\Http\Controllers\PartOneAutoMatchMaking;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\teamController;
use Illuminate\Routing\RouteGroup;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
Route::post('signup',[userController::class,'signup']);
Route::post('login',[userController::class,'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/self',[userController::class,'self']);
    Route::get('/logout',[userController::class,'logout']);
    Route::get('player/{id}',[playerController::class,'show']);
    Route::post('/prediction',[PredictionController::class,'post']);
    Route::get('topScorers',[playerController::class,'topScorers']);
    Route::get('topPlayer',[playerController::class,'topPlayer']);
    Route::get('team/{team}',[teamController::class,'show']);
    Route::post('team/{id}',[teamController::class,'uploadTeamLogo']);
    Route::get('part2/teams',[teamController::class,'part2Teams']);
    Route::get('part1/teamsSearchSuggestions',[teamController::class,'gradesAndClassesSuggestions']);
    Route::get('part1/teams',[teamController::class,'part1Teams']);
    Route::get('part1/unDeclaredMatches',[ContestController::class,'unDeclaredMatches']);
    Route::post('part1/declareMatch/{contest}',[ContestController::class,'declareMatch']);
    Route::get('finishedMatches',[ContestController::class,'finishedMatches']);
    Route::get('unFinishedMatches',[ContestController::class,'unFinishedMatches']);
    //admin---------------------------
    Route::post('excelInput',HandleExcelInput::class);
    Route::post('matchMaking',PartOneAutoMatchMaking::class);

});

