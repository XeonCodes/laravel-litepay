<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', fn(Request $request) => $request->user())->middleware('auth:sanctum');


// Route group for onboarding
Route::group(['prefix' => 'onboarding', 'namespace' => 'App\Http\Controllers'], function(){
    Route::post("/user", [UserController::class, "register"]);
    Route::post("/user/login", [UserController::class, "login"]);
});


// Verify without token
Route::group(['prefix' => 'verify', 'namespace' => 'App\Http\Controllers'], function(){
    Route::post("/otp", [UserController::class, "verifyOtp"]);
});

// Refresh apis here
Route::get("/monnify/refresh", [AdminController::class, "refreshMonnifyAccessToken"]);