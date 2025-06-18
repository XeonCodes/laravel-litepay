<?php

namespace App\Listeners;

use App\Events\NewCustomerCreated;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\GenerateController;
use App\Models\AdminModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateVirtualAccount
{
    
    protected $ApiController;
    protected $GenerateController;

    public function __construct(ApiController $ApiController, GenerateController $generateController)
    {
        $this->ApiController = $ApiController;
        $this->GenerateController = $generateController;
    }


    /**
     * Handle the event.
     */
    public function handle(NewCustomerCreated $event): void
    {
        $user = $event->user;
        $ref = $this->GenerateController->GenerateVirtualAccountReference();

        $admin = AdminModel::first();

        if (!$admin) {
            Log::error("Admin not found in the database");
            return;
        }

        // Make Api
        $apiCall = Http::withHeaders([
            "Authorization" => "Bearer {$admin->monnify_access_token}",
            "Content-Type" => "application/json",
            "Accept" => "application/json"
        ])->post(env("MONNIFY_URL") . "/api/v2/bank-transfer/reserved-accounts", [
            "accountReference" => $ref,
            "accountName" => "{$user->first_name} {$user->last_name}",
            "currencyCode" => "NGN",
            "contractCode" => env("MONNIFY_CONTRACT_CODE"),
            "customerEmail" => $user->email,
            "getAllAvailableBanks" => true,
            "bvn" => env("ADMIN_BVN") ,
        ]);

        $apiCallJson = $apiCall->json();

        if($apiCall['requestSuccessful'] == true){
            // Extract account details
            $accountNumber = $apiCallJson['responseBody']['accounts'][0]['accountNumber'] ?? null;
            $bank_name = $apiCallJson['responseBody']['accounts'][0]['bankName'] ?? null;

            if ($accountNumber && $bank_name) {
                // Save virtual account details to database
                DB::table('virtual_accounts')->insertOrIgnore([
                    "user_id" => $user->id,
                    "account_id" => $ref,
                    "account_reference" => $ref,
                    "account_number" => $accountNumber,
                    "bank_name" => $bank_name,
                    "account_name" => "{$user->first_name} {$user->last_name}",
                    "created_at" => now(),
                ]);

                Log::info("Virtual account created successfully for user: {$user->id} with reference: {$ref}");
               
            } else {
                Log::error("Failed to extract account details from API response", [
                    'response' => $apiCallJson
                ]);
            }
        }else {
            Log::error("Failed to create virtual account", [
                'status' => $apiCall->status(),
                'response' => $apiCallJson
            ]);
        }

    }
}
