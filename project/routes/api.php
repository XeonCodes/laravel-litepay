<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Route group for onboarding
Route::group(['prefix' => 'onboarding', 'namespace' => 'App\Http\Controllers'], function(){
    Route::post("/user", [UserController::class, "register"]);
});
