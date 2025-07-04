<?php

namespace App\Http\Controllers;

use App\Models\ResolveModel;
use App\Models\TransactionModel;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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


    // Resolve Cable
    public function ResolveCable(Request $request)
    {
        // Validate Request
        $validate = Validator::make($request->all(), [
            'iuc_number' => 'required|string',
            'provider_name' => 'required|string',
            'provider_code' => 'required|string',
            'plan_code' => 'required|string',
            'plan_name' => 'required|string',
        ]);

        Log::info($request->all());

        // Check Validation
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validate->errors()->first(),
            ], 400);
        }

        // Get user from request
        $user = $request->user();

        // Fetch cable plans from the controller
        $cablePlans = json_decode(file_get_contents(public_path('cable.json')), true);
        $provider_name = preg_replace('/\d/', '', $request->provider_name);
        // $dataPlans = $this->bundlePlansController->DataPlansUser();
        $planCablePlans = $cablePlans["TV_ID"][$provider_name][0]["PRODUCT"];

        // Find the selected plan using the plan_code
        $selectedPlan = null;
        foreach ($planCablePlans as $plan) {
            // Log::info($plan);
            if ($plan['PACKAGE_ID'] == $request->plan_code) {
                $selectedPlan = $plan;
                break;
            }
        }

        Log::info($selectedPlan);

        // If the plan is not found, return an error
        if (!$selectedPlan) {
            return response()->json([
                'status' => 404,
                'message' => 'Data plan not available.',
            ], 404);
        }

        $amount = $selectedPlan['PACKAGE_AMOUNT']; // Get the amount from the selected plan

        // Check if user has sufficient balance
        if ($user->balance < $amount) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient balance.',
            ], 400);
        }

        // Resolve meter
        $resolvecable = $this->apiCalls->ResolveCable($request->iuc_number, $request->provider_code);
        if($resolvecable['status'] !== true ){
            return response()->json([
               'status' => 400,
               'message' => $resolvecable['message']
            ], 400);
        }

        try {

            
            DB::beginTransaction();

            // Generate transaction reference
            $txf = $this->GenerateController->GenerateVirtualAccountReference();

            // Create pending transaction
            DB::table('resolve_transactions')->insertOrIgnore([
                "user_id" => $user->id,
                'txf' => $txf, // Generated transaction reference
                'amount' => $amount, // The amount paid
                'fee' => 0, // The calculated fee (if applicable)
                'beneficiary' => strtolower($request->iuc_number),
                'merchant' => $request->provider_name,
                'trans_type' => 'cable_resolve', // Type of transaction
                'status' => 'pending', // Transaction status
                "product" => $request->plan_name,
                'narration' => "Cable resolved for {$request->plan_name}", // Narration
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => "Cable subscription resolve initiated",
                'data' => [
                    'ref' => $txf,
                    'fee' => env("CABLE_FEE"),
                    'amount' => $amount,
                    'plan_name' => $request->plan_name,
                    'customer_name' => $resolvecable['customer_name']
                ],
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json([
                'status' => 500,
                'message' => "An error occurred. Please try again later.",
            ], 500);
        }
    }

    // Buy cable
    public function BuyCable(Request $request)
    {
        // Validate Request
        $validate = Validator::make($request->all(), [
            'txf' => ['required'],
            'iuc_number' => 'required|string',
            'provider_name' => 'required|string',
            'provider_code' => 'required|string',
            'plan_code' => 'required|string',
            'plan_name' => 'required|string',
        ]);

        try {
        
            // Check Validation
            if ($validate->fails()) {
                return response()->json([
                    'status' => 400,
                    'message' => $validate->errors()->first(),
                ], 400);
            }

            // Get user from request
            $user = $request->user();

            // Resolve transaction from resolve table
            $resolve = ResolveModel::where('txf', $request->txf)
                ->where('user_id', $user->id)
                ->first();

            // Check if the transaction exists and is not processed
            if (!$resolve || $resolve->status == 'processed') {
                return response()->json([
                    "status" => 400,
                    "message" => "Invalid or already processed transaction"
                ], 400);
            }

            // Check if the transaction is older than 5 minutes
            $transactionTime = Carbon::parse($resolve->created_at);
            $currentTime = Carbon::now();

            if ($transactionTime->diffInMinutes($currentTime) > 5) {
                // Log::error("Transaction expired: Created more than 2 minutes ago");
                return response()->json([
                    "status" => 400,
                    "message" => "Expired transaction. Exceeded 5 minutes"
                ], 400);
            }

            // Fetch cable plans from the controller
            $cablePlans = json_decode(file_get_contents(public_path('cable.json')), true);
            $provider_name = preg_replace('/\d/', '', $request->provider_name);
            // $dataPlans = $this->bundlePlansController->DataPlansUser();
            $planCablePlans = $cablePlans["TV_ID"][$provider_name][0]["PRODUCT"];

            // Find the selected plan using the plan_code
            $selectedPlan = null;
            foreach ($planCablePlans as $plan) {
                // Log::info($plan);
                if ($plan['PACKAGE_ID'] == $request->plan_code) {
                    $selectedPlan = $plan;
                    break;
                }
            }


            // If the plan is not found, return an error
            if (!$selectedPlan) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Data plan not found.',
                ], 404);
            }

            $amount = $selectedPlan['PACKAGE_AMOUNT']; // Get the amount from the selected plan

            // Check if user has sufficient balance
            if ($user->balance < $amount) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Insufficient balance.',
                ], 400);
            }

            $created_at = now();

            // Use a database transaction to prevent race conditions
            DB::beginTransaction();

            try {

                // Resolve transaction from resolve table
                $transaction = ResolveModel::where('txf', $request->txf)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                // Check if the transaction exists and is not processed
                if (!$transaction || $transaction->status == 'processed') {
                    throw new Exception("Invalid or already processed transaction", 400);
                }

                // Current balance
                $currentBalance = $user->balance;

                // Debit amount
                $debit = $amount;

                // Debit the user's account
                $user->balance -= $debit;
                $user->save();

                // Create transaction record
                DB::table('transactions')->insertOrIgnore([
                    "user_id" => $user->id,
                    "type" => "debit",
                    "txf" => $transaction->txf,
                    "beneficiary" => $transaction->beneficiary,
                    "balance_before" => $currentBalance,
                    "balance_after" => $user->balance,
                    "amount" => $debit,
                    "trans_type" => "cable_purchase",
                    "status" => "pending",
                    "merchant" => $transaction->merchant,
                    "product" => $transaction->product,
                    "title" => $transaction->product,   
                    "created_at" => $created_at,
                ]);

                // Commit the transaction
                DB::commit();
            } catch (Exception $e) {
                // Rollback the transaction in case of an error
                DB::rollBack();
                throw new Exception($e->getMessage(), 500);
            }

            // Buy airtime
            try {

                // Log::info("Plan code: $request->plan_code");

                // Send money to bank
                $response = $this->apiCalls->BuyCableClubKon(
                    $user->phone_number,
                    $transaction->beneficiary,
                    $transaction->merchant,
                    $request->plan_code,
                    $transaction->txf
                );

                // Check if the API call was successful
                if ($response['status'] !== true) {
                    // Refund
                    // $this->apiCalls->refundUser($amount, $user->id, "Cable");
                    throw new Exception($response['message'], 403);
                }

                // Save x_ref
                $trans = TransactionModel::where('txf', $transaction->txf)->first();
                $trans->x_ref = $response['x_ref'];
                $trans->amount_sent = $response['amount'];
                $trans->save();

                // Update the transaction_resolve status
                $resolve->status = 'processed';
                $resolve->save();

            } catch (Exception $e) {
                throw new Exception("Failed to process cable subscription: " . $e->getMessage(), 500);
            }

            // Log
            // Log::info(
            //     "Data purchase Initiated: {$transaction->txf}",
            //     [
            //         'user_id' => $user->id?? null,
            //         'txf' => $request->txf?? null,
            //     ]
            // );

            // Return success message
            return response()->json([
                'status' => 200,
                'message' => "Cable subscription initiated successfully",
                "data" => [
                    "object" => [
                        "amount" => $transaction->amount,
                        "ref" => $transaction->txf,
                        "status" => "Successful",
                        "created_at" => $created_at,
                        "fee" => $transaction->fee,
                    ],
                    "mobile_data" => [
                        [
                            "name" => "Status",
                            "value" => "pending"
                        ],
                        [
                            "name" => "IUC NO",
                            "value" => $transaction->beneficiary,
                        ],
                        [
                            "name" => "Provider",
                            "value" => strtoupper($transaction->merchant),
                        ],
                        [
                            "name" => "Plan",
                            "value" => strtoupper($transaction->product),
                        ],
                        [
                            "name" => "Amount",
                            "value" => "₦$debit",
                        ],
                        [
                            "name" => "Fee",
                            "value" => "₦".env("CABLE_FEE"),
                        ],
                        [
                            "name" => "Ref",
                            "value" => $transaction->txf
                        ]
                    ],
                    "user" => $user

                ],
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json([
               'status' => 500,
               'message' => "An error occurred. Please try again later."
            ], 500);
        }
    }



    // Resolve electricity
    public function ResolveElectricity(Request $request){

        // Validate Request
        $validate = Validator::make($request->all(), [
            'meter_number' => 'required|string',
            'amount' => 'required|numeric|min:500',
            'company_name' => 'required|string',
            'company_code' => 'required|string',
        ]);

        // Check Validation
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validate->errors()->first(),
            ], 400);
        }

        // Get user from request
        $user = $request->user();

        // Fee
        $fee = env("ELECTRICITY_FEE");

        // Resolve meter
        $resolvemeter = $this->apiCalls->ResolveElectricy($request->meter_number, $request->company_code);
        if($resolvemeter['status'] !== true ){
            return response()->json([
               'status' => 400,
               'message' => $resolvemeter['message']
            ], 400);
        }

        try {

            DB::beginTransaction();

            // Generate txf
            $txf = $this->GenerateController->GenerateVirtualAccountReference();

            // Create pending transaction
            DB::table('resolve_transactions')->insertOrIgnore([
                "user_id" => $user->id,
                'txf' => $txf,
                'amount' => $request->amount,
                'fee' => $fee,
                'beneficiary' => strtolower($request->meter_number),
                'merchant' => $request->company_name,
                'trans_type' => 'electricity_resolve',
                'status' => 'pending',
                'narration' => "Electricity resolved",
                'created_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => "Electricity process initiated",
                'data' => [
                    'ref' => $txf,
                    'fee' => $fee,
                    'customer_name' => $resolvemeter['customer_name']
                ]
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json([
               'status' => 500,
               'message' => "An error occurred. Please try again later."
            ], 500);
        }

    }

    // Buy Electricity
    public function BuyElectricity(Request $request){

        // Validate Request
        $validate = Validator::make($request->all(), [
            'txf' => ['required'],
            'amount' => 'required|numeric|min:50',
            'company_code' => 'required|string',
        ]);

        try {
        

            // Check Validation
            if ($validate->fails()) {
                return response()->json([
                    'status' => 400,
                    'message' => $validate->errors()->first(),
                ], 400);
            }

            // Get user from request
            $user = $request->user();

            // Resolve transaction from resolve table
            $resolve = ResolveModel::where('txf', $request->txf)
                ->where('user_id', $user->id)
                ->first();

            // Check if the transaction exists and is not processed
            if (!$resolve || $resolve->status == 'processed') {
                return response()->json([
                    "status" => 400,
                    "message" => "Invalid or already processed transaction"
                ], 400);
            }

            // Check if the transaction is older than 5 minutes
            $transactionTime = Carbon::parse($resolve->created_at);
            $currentTime = Carbon::now();

            if ($transactionTime->diffInMinutes($currentTime) > 5) {
                // Log::error("Transaction expired: Created more than 2 minutes ago");
                return response()->json([
                    "status" => 400,
                    "message" => "Expired transaction. Exceeded 5 minutes"
                ], 400);
            }

            // Check if the user has sufficient balance
            if ($user->balance < ($resolve->amount + $resolve->fee)) {
                return response()->json([
                    "status" => 400,
                    "message" => "Insufficient balance"
                ], 400);
            }

            $created_at = now();

            // Use a database transaction to prevent race conditions
            DB::beginTransaction();

            try {

                // Resolve transaction from resolve table
                $transaction = ResolveModel::where('txf', $request->txf)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                // Check if the transaction exists and is not processed
                if (!$transaction || $transaction->status == 'processed') {
                    throw new Exception("Invalid or already processed transaction", 400);
                }

                // Current balance
                $currentBalance = $user->balance;

                // Debit amount
                $debit = $transaction->amount + $transaction->fee;

                // Debit the user's account
                $user->balance -= $debit;
                $user->save();

                // Create transaction record
                DB::table('transactions')->insertOrIgnore([
                    "user_id" => $user->id,
                    "type" => "debit",
                    "txf" => $transaction->txf,
                    "beneficiary" => $transaction->beneficiary,
                    "balance_before" => $currentBalance,
                    "balance_after" => $user->balance,
                    "amount" => $debit,
                    // "bonus" => $transaction->bonus,
                    "fee" => env("ELECTRICITY_FEE"),
                    "trans_type" => "electricity_purchase",
                    "status" => "pending",
                    "merchant" => $transaction->merchant,
                    "title" => "₦$debit $resolve->merchant Electricity",   
                    "created_at" => $created_at,
                ]);

                // Commit the transaction
                DB::commit();
            } catch (Exception $e) {
                // Rollback the transaction in case of an error
                DB::rollBack();
                throw new Exception($e->getMessage(), 500);
            }

            // Buy electricity
            try {

                // Send money to bank
                $response = $this->apiCalls->BuyElectricityClubKon(
                    $transaction->amount,
                    $user->phone_number,
                    $transaction->beneficiary,
                    $request->company_code,
                    $transaction->txf,
                );

                // Check if the API call was successful
                if ($response['status'] !== true) {
                    // $this->apiCalls->refundUser($debit, $user->id, "Electricity");
                    $trans = TransactionModel::where('txf', $transaction->txf)->first();
                    $trans->status = "failed";
                    $trans->save();
                    throw new Exception($response['message'], 403);
                }

                // Save x_ref
                $trans = TransactionModel::where('txf', $transaction->txf)->first();
                $trans->x_ref = $response['x_ref'];
                $trans->amount_sent = $response['amount'];
                $trans->save();

                // Update the transaction status
                $transaction->status = 'processed';
                $transaction->save();

            } catch (Exception $e) {
                throw new Exception("Failed to process electricity purchase: " . $e->getMessage(), 500);
            }

            // Log
            Log::info(
                "Electricity purchase Initiated: {$transaction->txf}",
                [
                    'user_id' => $user->id?? null,
                    'txf' => $request->txf?? null,
                ]
            );

            // Return success message
            return response()->json([
                'status' => 200,
                'message' => "Electricity purchase initiated successfully",
                "data" => [
                    "object" => [
                        "amount" => $transaction->amount,
                        "ref" => $transaction->txf,
                        "status" => "pending",
                        "created_at" => $created_at,
                        "fee" => $transaction->fee,
                    ],
                    "mobile_data" => [
                        [
                            "name" => "Status",
                            "value" => "Pending"
                        ],
                        [
                            "name" => "Meter",
                            "value" => $transaction->beneficiary,
                        ],
                        [
                            "name" => "Company",
                            "value" => strtoupper($transaction->merchant),
                        ],
                        [
                            "name" => "Amount",
                            "value" => "₦$debit",
                        ],
                        [
                            "name" => "Fee",
                            "value" => "₦$transaction->fee",
                        ],
                        [
                            "name" => "Ref",
                            "value" => $transaction->txf
                        ]
                        
                    ],
                    "user" => $user

                ],
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json([
               'status' => 500,
               'message' => "An error occurred. Please try again later."
            ], 500);
        }


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
