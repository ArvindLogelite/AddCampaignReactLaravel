<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ReactController;
use App\Models\Category;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('categories', [ReactController::class, 'store']);

Route::get('/campaign_list', function () {
    $data = Category::orderBy('created_at', 'desc')->get();
    return response()->json($data);
});

Route::delete('/campaign/{id}', [ReactController::class, 'destroy']);
Route::put('/campaign/{id}',[ReactController::class,'updateStatus']);
Route::get('campaign/{id}/show',[ReactController::class,'showsingledata']);
Route::post('campaign',[ReactController::class,'update']);
