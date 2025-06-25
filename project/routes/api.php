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


// {"eventData":{"product":{"reference":"5G4PAWRM2EVDTKF","type":"RESERVED_ACCOUNT"},"transactionReference":"MNFY|15|20250625041413|280169","paymentReference":"MNFY|15|20250625041413|280169","paidOn":"2025-06-25 04:14:14.0","paymentDescription":"ebi","metaData":[],"paymentSourceInformation":[{"bankCode":"50211","amountPaid":450,"accountName":"EBIMINI, TAMARAU-EMI","sessionId":"090267250625031411709049606227","accountNumber":"2049606227"}],"destinationAccountInformation":{"bankCode":"50515","bankName":"Moniepoint Microfinance Bank","accountNumber":"6027552323"},"amountPaid":450,"totalPayable":450,"cardDetails":[],"paymentMethod":"ACCOUNT_TRANSFER","currency":"NGN","settlementAmount":"442.74","paymentStatus":"PAID","customer":{"name":"Tamarauemi Ebimini","email":"emylinpeter1@gmail.com"}},"eventType":"SUCCESSFUL_TRANSACTION"} 