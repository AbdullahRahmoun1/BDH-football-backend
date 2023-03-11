<?php

use App\Http\Controllers\HandleExcelInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\playerController;
use App\Http\Controllers\PartOneAutoMatchMaking;
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
Route::post('login',[userController::class,'login']);
Route::post('signup',[userController::class,'signup']);
Route::post('matchMaking',PartOneAutoMatchMaking::class);
Route::post('excelInput',HandleExcelInput::class);
Route::get('player/{id}',[playerController::class,'show']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('lsjfsd',function (){
    
});

