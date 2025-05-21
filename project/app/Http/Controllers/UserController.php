<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{


    // Import Controllers
    protected $GenerateContoller;
    protected $ApiController;
    protected $VerifyController;

    public function __construct(GenerateController $GenerateContoller, ApiController $ApiController, VerifyController $VerifyController){
        $this->GenerateContoller = $GenerateContoller;
        $this->ApiController = $ApiController;
        $this->VerifyController = $VerifyController;
    }

    // Get user balance
    public function register(Request $request){
        try {

            DB::beginTransaction();

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

            $otp = $this->GenerateContoller->GenerateOtp();

            $invitedBy = "";

            if($request->invited_by){
                $check = $this->VerifyController->VerifyPromoCode($request->invited_by);
                if($check){
                    $invitedBy = $request->invited_by;
                }
            }

            // Create a new user
            $user = User::create([
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "email" => $request->email,
                "username" => $request->username,
                "phone_number" => $request->phone_number,
                "password" => bcrypt($request->password),
                "promo_code" => $this->GenerateContoller->GeneratePromoCode(),
                "invited_by" => $invitedBy,
                "otp" => bcrypt($otp)
            ]);

            // Notification
            

            // Email notification

            DB::commit();
            
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
            DB::rollBack();
            Log::error("Error in UserController@register: " . $error->getMessage());
            return response()->json([
                    "status" => 500,
                    "message" => $error->getMessage()
                ], 500);
        }
    }
}
