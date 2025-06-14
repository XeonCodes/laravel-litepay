<?php

namespace App\Http\Controllers;

use App\Models\AdminModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{

    // Refresh monnify access token
    public function refreshMonnifyAccessToken(){


        // Check if last refresh time has exceeeded 20 minutes
        $admin = AdminModel::first();
        if(!$admin){
            Log::error("Admin not found in the database");
            return response()->json([
                "status" => false,
                "message" => "Bad request"
            ], 404);
        }

        $lastRefreshTime = $admin->monnify_access_token_lasttime;
        if ($lastRefreshTime && Carbon::parse($lastRefreshTime)->gt(now()->subMinutes(20))) {
            Log::info($admin->monnify_access_token_lasttime);
            Log::info("Monnify access token is still valid, no need to refresh. Minutes since last refresh: " . now()->diffInMinutes(Carbon::parse($lastRefreshTime)));
            return false;
        }

        $request = Http::withHeaders([
            "Authorization" => "Basic ".env("MONNIFY_BASE64"),
            "Content-Type" => "application/json"
        ])->post(env("MONNIFY_URL")."/api/v1/auth/login");

        if($request->successful()){
            $request = $request->json();
            $accessToken = $request['responseBody']['accessToken'];

            // Update the admin model with the new access token and last refresh time
            $admin->monnify_access_token = $accessToken;
            $admin->monnify_access_token_lasttime = now();
            $admin->save();

            Log::info("Monnify token refreshsed successfully");
            return true;

            // return response()->json([
            //     "status" => true,
            //     "message" => "Monnify access token refreshed successfully",
            //     "access_token" => $accessToken
            // ]);
        }

        // You can send an email to the admin or log the error.
        Log::error("Monnify access token refresh failed", [
            "status" => $request->status(),
            "body" => $request->body()
        ]);


    }

}
