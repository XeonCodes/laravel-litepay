<?php

namespace App\Http\Controllers;

use App\Models\TransactionModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\matches;

class VTUController extends Controller
{

    protected $GenerateController;

    protected $dataBundle;

    protected $apiCalls;

    public function __construct(GenerateController $generate_controller, DataBundleController $data_bundle, ApiController $api_controller){
        $this->GenerateController = $generate_controller;
        $this->dataBundle = $data_bundle;
        $this->apiCalls = $api_controller;
    }
    
    public function BuyAirtime(Request $request){

        $validate = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'phone_number' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/',
            'network_id' => 'required|string|in:mtn,airtel,glo,9mobile',
        ]);

        // Check if validation fails
        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => $validate->errors()->first()
            ], 400);
        }

        // Check if the amount is less than 50
        if($request->amount < 50) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot buy airtime less than 50 naira'
            ], 400);
        }

        // Get the user from the request
        $user = $request->user();

        // Check if the user has sufficient balance
        if($user->balance < $request->amount) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance'
            ], 400);
        }

        $created_at = now();

        // Begin Transaction
        DB::beginTransaction();

        try {
            
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            // Debit the user's balance
            $user->balance -= $request->amount;
            $user->save();

            // Generate Reference
            $ref = $this->GenerateController->GenerateVirtualAccountReference();

            // Create transaction record
            DB::table('transactions')->insertOrIgnore([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'ref' => $ref,
                'type' => 'debit',
                'trans_type'=> 'airtime',
                'status' => 'pending',
                'merchant' => $request->network_id,
                'beneficiary' => $request->phone_number,
                'product' => "N$request->amount Airtime purchase",
                'narration' => "N$request->amount Airtime purchase",
                'created_at' => $created_at,
            ]);

            // Save to DB
            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ]);
        }

        $network = "";
        $network_id = $request->network_id;

        switch ($network_id) {
            case "mtn":
                $network = "1";
                break;
            case "airtel":
                $network = "4";
                break;
            case "glo":
                $network = "2";
                break;
            case "9mobile":
                $network = "3";
                break;
            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Bad request'
                ], 400);
        }

        // Purchase airtime for this user
        $buy = Http::withHeaders([
            "Authorization" => "Token ". env("DATASTATION_API"),
            "Content-Type" => "application/json"
        ])->post(env("DATASTATION_URL") . "/topup", [
            "network" => $network,
            "amount" => $request->amount,
            "mobile_number" => $request->phone_number,
            "Ported_number" => "tru",
            "airtime_type" => "VTU"
        ]);

        $json = $buy->json();

        if (isset($json['Status']) && $json['Status'] === 'successful') {

            // Email to customer

            // Push notification

            // Update transaction status
            $transaction = TransactionModel::where('ref', $ref)->first();
            $transaction->status = "successful";
            $transaction->profit = $request->amount - $json['paid_amount'];
            $transaction->x_ref = $json['ident'];
            $transaction->save();

        }else {
            // Refund
            $user->balance += $request->amount;
            $user->save();
        }

        // Response
        return response()->json([
            "status" => 200,
            "message" => "Airtime bought successfully",
        ], 200);

    }


    // Buy Data Plans
    public function BuyData(Request $request) {

        $validate = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/',
            'network_id' => 'required|string|in:mtn,airtel,glo,9mobile',
            'plan_id' => 'required|string'
        ]);

        if($validate->fails()){
            return response()->json([
                "status" => 400,
                "message" => $validate->errors()->first()
            ], 400);
        }

        // Get user
        $user = $request->user();

        // Get data plan using the network id and plan id

        $network_id = preg_replace('/\d/', '', $request->network_id);
        $dataPlans = $this->dataBundle->DataPlansUser();
        $planfromDataPlans = $dataPlans[$network_id][0]["PRODUCT"];

        // Find the selected plan using the plan_code
        $selectedPlan = null;
        foreach ($planfromDataPlans as $plan) {
            // Log::info($plan);
            if ($plan['PRODUCT_ID'] == $request->plan_code) {
                $selectedPlan = $plan;
                break;
            }
        }

        // If the plan is not found, return an error
        if (!$selectedPlan) {
            return response()->json([
                'status' => 404,
                'message' => 'Bad request',
            ], 404);
        }

        // Check if user has sufficient amount 
        if($user->balance < $selectedPlan['PRODUCT_AMOUNT']){
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient balance',
            ], 400);
        }


        // Current date time
        $created_at = now();
        $ref = $this->GenerateController->GenerateVirtualAccountReference();

        DB::beginTransaction();

        try {
            
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            // Debit user
            $user->balance -= $selectedPlan['PRODUCT_AMOUNT'];
            $user->save();

            // Transaction table
            DB::table('transactions')->insertOrIgnore([
                'user_id' => $user->id,
                'amount' => $selectedPlan['PRODUCT_AMOUNT'],
                'ref' => $ref,
                'type' => 'debit',
                'trans_type'=> 'data',
                'status' => 'pending',
                'merchant' => $request->network_id,
                'beneficiary' => $request->phone_number,
                'product' => $selectedPlan['PRODUCT_NAME'],
                'narration' => $selectedPlan['PRODUCT_NAME'],
                'created_at' => $created_at,
            ]);

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Try again!',
            ], 500);
        }


        // Buy the plan.
        $response = match ($selectedPlan['PRODUCT_SOURCE']) {
            1 => $this->apiCalls->BuyDataStation(
                $request->phone_number,
                $request->network_id,
                $request->plan_code,
            ),
            2 => $this->apiCalls->BuyDataGladT(
                $request->phone_number,
                $request->network_id,
                $request->plan_code,
                $request->txf
            ),
            3 => $this->apiCalls->BuyDataMostCheap(
                $request->phone_number,
                $request->network_id,
                $request->plan_code,
                $request->txf
            )
        };

        if(!$response['status']){
            // Refund
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Try again!',
            ], 500);
        }


        // Update transaction status
        $transaction = TransactionModel::where("ref", $ref)->first();

        if(!$transaction){

            // Log

            // Email

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Try again!',
            ], 500);
        }


        $transaction->status = "successful";
        $transaction->profit = $selectedPlan['PRODUCT_AMOUNT'] - $response['amount'];
        $transaction->save();

        // Create notification

        // Send email

        // Send Push Notification

        return response()->json([
            "status" => 200,
            "message" => "Data bought successfully",
            "data" => []
        ], 200);


    }


}







// <?php

// use App\Http\Controllers\AdminController;
// use App\Http\Controllers\UserController;
// use App\Http\Controllers\VTUController;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

// Route::get('/user', fn(Request $request) => $request->user())->middleware('auth:sanctum');

// // Route group for onboarding
// Route::group(['prefix' => 'onboarding', 'namespace' => 'App\Http\Controllers'], function(){
//     Route::post("/user", [UserController::class, "register"]);
//     Route::post("/user/login", [UserController::class, "login"]);
// });

// // Verify without token
// Route::group(['prefix' => 'verify', 'namespace' => 'App\Http\Controllers'], function(){
//     Route::post("/otp", [UserController::class, "verifyOtp"]);
// });

// // Refresh apis here
// Route::get("/monnify/refresh", [AdminController::class, "refreshMonnifyAccessToken"]);


// // Route group for VTU
// Route::middleware(['auth:sanctum'])->prefix('vtu')->namespace('App\Http\Controllers')->group(function () {
//     Route::post("/airtime", [VTUController::class, "BuyAirtime"]);
// });
