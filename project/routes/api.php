<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Router::get("/userbalance", [UserController::class, "getUserBalance"]);