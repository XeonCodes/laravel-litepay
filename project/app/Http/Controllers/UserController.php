<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Get user balance
    public function register(Request $request){
        try {

            // Validate the request
            $validate = Validator::make($request->all(), [
                "first_name" => "required|string|max:15",
                "last_name" => "required|string|max:15",
                "email" => "required|email|unique:users,email",
                "username" => "required|unique:users,username|min3|max:15",
                "phone_number" => "required|unique:users,phone_number|regex:/^0[0-9]{10}$/",
                "password" => "required|string|min:6"
            ]);

            
            // Check if validation fails
            if($validate->fails()){
                return response()->json([
                    "status" => 422,
                    "message" => $validate->errors()
                ], 422);
            }

            // Success response
            return response()->json([
                "status" => 200,
                "message" => "User registered successfully",
                "data" => [
                    "first_name" => $request->first_name,
                    "last_name" => $request->last_name,
                    "email" => $request->email,
                    "username" => $request->username,
                    "phone_number" => $request->phone_number
                ]
            ], 200);

        } catch (Exception $error) {
            Log::error("Error in UserController@register: " . $error->getMessage());
            return response()->json([
                    "status" => 500,
                    "message" => $error->getMessage()
                ], 500);
        }
    }
}
