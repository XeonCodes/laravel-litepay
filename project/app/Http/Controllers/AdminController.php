<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{

    // Refresh monnify access token
    public function refreshMonnifyAccessToken(){

        $request = Http::withHeaders([
            "Authorization" => "Basic ".env("MONNIFY_BASE64"),
            "Content-Type" => "application/json"
        ])->post(env("MONNIFY_URL")."/api/v1/auth/login");

        if($request->successful()){
            $request = $request->json();
            $accessToken = $request['responseBody']['accessToken'];

            return response()->json([
                "status" => true,
                "message" => "Monnify access token refreshed successfully",
                "access_token" => $accessToken
            ]);
        }

        // You can send an email to the admin or log the error.
        Log::error("Monnify access token refresh failed", [
            "status" => $request->status(),
            "body" => $request->body()
        ]);


    }

}
