<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // Get user balance
    public function getUserBalance(Request $request){
        try {

            $userId = $request->input("user_id");
            $user = User::where("id", $userId)->first();

            if(!$user){
                return response()->json([
                    "status" => 404,
                    "message" => "Bad request",
                ], 400);
            }

            return response()->json([
                "status" => 200,
                "message" => $user->balance,
            ], 200);
        } catch (Exception $error) {
            // Log error to the server
            Log::error($error->getMessage());
            return response()->json([
                "status" => 500,
                "message" => "Something went wrong",
            ], 500);
        }
    }
}
