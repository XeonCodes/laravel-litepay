<?php

namespace App\Http\Controllers;

use App\Mail\GeneralMail;
use App\Models\NotificationModel;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
                "username" => "required|unique:users,username|min:3|max:15",
                "phone_number" => "required|regex:/^0[0-9]{10}$/",
                "password" => "required|string|min:6"
            ]);

            // Check if validation fails
            if($validate->fails()){
                return response()->json([
                    "status" => 422,
                    "message" => $validate->errors()->first()
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
                "otp" => bcrypt(value: $otp)
            ]);

            // Notification
            NotificationModel::create([
                "user_id" => $user->id,
                "title" => "Welcome to Our Service",
                "message" => "Hello {$user->first_name}, welcome to our service! Your account has been created successfully.",
                "type" => "welcome"
            ]);
            
            // Email notification
            try {
                $message = "Welcome to our service! Your account has been created successfully. Your OTP is: {$otp}";
                Mail::to($request->email)->send(new GeneralMail($message, strtoupper($request->username), "Welcome to " . env("APP_NAME") ));
            } catch (Exception $th) {
                Log::error("Error sending email: $th");
            }

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


    // Login function
    public function login(Request $request){

        // Validation rule
        $validate = Validator::make($request->all(), [
            "email" => "required|string|email",
            "password" => "required|string"
        ]);

        // Check if validation is successful.
        if($validate->fails()){
            return response()->json([
                "status" => 422,
                "message" => $validate->errors()->first()
            ]);
        }

        // Resolve user
        $user = User::where("email", $request->input("email"))->first();

        // Check if user exist
        if(!$user){
            return response()->json([
                "status" => 404,
                "message" => "Email or password is incorrect"
            ], 404);
        }

        // Check if email and password correct
        if(!Hash::check($request->password, $user->password)){
            return response()->json([
                "status" => 401,
                "message" => "Email or password is incorrect"
            ], 401);
        }

        $otp = $this->GenerateContoller->GenerateOtp();
        try {
            $message = "Welcome back to ".env("APP_NAME")." Your OTP is $otp";
            Mail::to($user->email)->send(new GeneralMail($message, $user->username, "Welcome Back"));
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }

        // Return a success response with the user Payload
        return response()->json([
            "status" => 200,
            "message" => "Login successful",
            "data" => $user
        ], 200);


    }


    

}


