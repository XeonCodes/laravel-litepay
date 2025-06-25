<?php

namespace App\Http\Controllers;

use App\Mail\GeneralMail;
use App\Models\AdminModel;
use App\Models\TransactionModel;
use App\Models\User;
use App\Models\VirtualAccountModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WebhookController extends Controller
{

    protected $GenerateController;
    protected $ApiController;

    public function __construct(GenerateController $GenerateController, ApiController $ApiController){
        $this->GenerateController = $GenerateController;
        $this->ApiController = $ApiController;
    }

    // Flw Hook
    public function FlwHook(Request $request)
    {
        // Retrieve the signature sent by Flutterwave
        $flutterwaveSignature = $request->header('verif-hash');

        // Get the secret hash from environment variables
        $secretHash = env('FLW_SECRET_HASH');

        // Validate the signature
        if (!$flutterwaveSignature || $flutterwaveSignature !== $secretHash) {
            // Invalid signature, log and abort
            Log::error('Invalid Flutterwave webhook signature.');
            return response()->json([
                'status' => 403,
                'message' => 'Invalid signature'
            ], 403);
        }

        // Log the incoming payload for debugging purposes
        Log::info('Flutterwave Webhook Payload:', $request->all());

        // Extract the data from the webhook request
        $data = $request->input('data');

        // Check if the payment was successful
        if ($data['status'] === 'successful') {

            // Extract relevant information from the webhook payload
            $transactionId = $data['id']; // Flutterwave transaction ID

            // Verify payment using Flutterwave's API (optional, for extra security)
            $verificationResponse = Http::withToken(env('FLW_SEC_KEY'))
                ->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify");

            if ($verificationResponse->successful() && $verificationResponse['data']['status'] === 'successful') {

                // Check if the transaction already exists
                $existingTransaction = TransactionModel::where('x_ref', $transactionId)
                    ->where('status', 'successful')
                    ->first();

                if ($existingTransaction) {
                    Log::error("Transaction already exists.");
                    return response()->json([
                        'status' => 403,
                        'message' => ''
                    ], 403);
                }

                // Get the customer to whom the money was sent to
                $account_ref = VirtualAccountModel::where('account_reference', $request->data['tx_ref'])->first();

                $beneficiary = User::where('id', $account_ref->user_id)->first();

                // Calculate amount to credit the customer
                $amount_sent = $request->data['amount'];
                $fee1 = $request->data['app_fee'] * 0.075;
                $fee = $request->data['app_fee'] + $fee1;
                $sent_amount = $request->data['amount'] - $fee;

                $old_balance = $beneficiary->balance;
                $new_balance = $beneficiary->balance + $sent_amount;

                // Credit the customer
                $beneficiary->balance += $sent_amount;
                $beneficiary->save();

                $txf = $this->GenerateController->GenerateVirtualAccountReference();

                // Create Transaction in transaction table
                DB::table('transactions')->insertOrIgnore([
                    'type' => "credit",
                    'user_id' => $beneficiary->id,
                    'txf' => $txf,
                    'x_ref' => $request->data['id'],
                    'balance_before' => $old_balance,
                    'balance_after' => $new_balance,
                    'fee' => $fee,
                    'amount' => $sent_amount,
                    'title' => "₦$sent_amount deposit",
                    'trans_type' => 'funding',
                    'amount_sent' => $request->data['amount'],
                    'status' => "successful",
                    'created_at' => $request->data['created_at'],
                    'narration' => "₦$sent_amount deposit"
                ]);

                // Send Push to receiver (Receiver)
                // $beneficiary->receive_push == 1 && $this->Services->SendPushNotification($beneficiary->device_id, "Your account have been successfully credited with the sum of NGN" . number_format($sent_amount, 2, '.', ''), "Credit Alert");

                // Create Notification
                DB::table('notifications')->insertOrIgnore([
                    'user_id' => $beneficiary->id,
                    'ref' => $this->GenerateController->GenerateVirtualAccountReference(),
                    'title' => "Successful Wallet Funding",
                    'created_at' => now(),
                    'img' => env("APP_URLAPI") . "/funding_success.jpg",
                    'message' => "Your transfer of $amount_sent was received and your balance has been successfully credited.",
                ]);

                // $trans_data = [
                //     "txf" => $txf,
                //     "created_at" => $request->data['created_at'],
                //     "amount" => $request->data['amount'],
                //     "status" => $request->data['status'],
                //     "narration" => "Fund from bank deposit",
                // ];
                // Convert the associative array to an object
                // $trans_data_object = (object) $trans_data;

                try {
                    //code...
                    // Send transaction email to user
                    Mail::to($beneficiary->email)->send(new GeneralMail("Your transfer of $amount_sent was received and your balance has been successfully credited.", $beneficiary->username, subject: "Credit Alert"));
                    Mail::to(env("ADMIN_EMAIL"))->send(new GeneralMail("A customer account has been funded with the sum of $amount_sent.", "ADMIN", subject: "Customer Wallet Funding"));
                } catch (\Throwable $th) {
                    //throw $th;
                    Log::error("Could not send an email to a customer who funded wallet.");
                }

                try {
                    // Send push notification to receiver
                    $this->ApiController->SendPushNotification($beneficiary->device_id, "Credit Alert", "You have successfully received $amount_sent into your " . env("APP_NAME") . " account", ["data" => "test"]);
                } catch (\Throwable $th) {
                }

                // Send transaction email to admin
                // Mail::to(env("ADMIN_EMAIL"))->send(new AdminTransactionMail($beneficiary, $trans_data_object));

            }else {
                Log::error('Failed to verify payment from Flutterwave.');
                return response()->json([
                    'status' => 500,
                    'message' => ''
                ], 500);
            }

        }else{
            Log::info('Payment was not successful:', $request->all());
            return response()->json([
                'status' => 400,
                'message' => ''
            ], 400);
        }
        
    }

    // Monnify Webhook
    public function MonnifyWebhook(Request $request)
    {

        // Log the payload for debugging
        Log::info('Monnify Webhook Payload:', $request->all());

        // Validate the event type
        $status = $request->eventType;
        if ($status !== "SUCCESSFUL_TRANSACTION") {
            Log::error('Unsuccessful Monnify webhook event.', ['eventType' => $status]);
            return response()->json([
                'status' => 403,
                'message' => 'Invalid event type'
            ], 403);
        }

        // Validate the event data
        $body = $request->eventData;
        if (empty($body) || !isset($body['transactionReference'], $body['paymentStatus'], $body['customer']['email'], $body['settlementAmount'], $body['amountPaid'], $body['paymentSourceInformation'][0])) {
            Log::error('Invalid Monnify webhook payload.', ['body' => $body]);
            return response()->json([
                'status' => 400,
                'message' => 'Invalid payload'
            ], 400);
        }

        // Verify the transaction reference
        $verificationResponse = $this->verifyMonnify($body['transactionReference']);
        if (!$verificationResponse['status']) {
            Log::error('Monnify webhook event verification failed.', ['message' => $verificationResponse['message']]);
            return response()->json([
                'status' => 500,
                'message' => 'Webhook: Failed to verify transaction reference'
            ], 500);
        }

        // Check if the transaction already exists
        $transaction = TransactionModel::where('x_ref', $body['transactionReference'])->first();
        if ($transaction) {
            Log::error('Monnify webhook event transaction already exists.', ['transactionReference' => $body['transactionReference']]);
            return response()->json([
                'status' => 403,
                'message' => 'Transaction already exists'
            ], 403);
        }

        // Find the user
        $user = User::where('email', strtolower($body['customer']['email']))->lockForUpdate()->first();
        if (!$user) {
            Log::error('Monnify webhook event user not found.', ['email' => $body['customer']['email']]);
            return response()->json([
                'status' => 404,
                'message' => 'User not found'
            ], 404);
        }

        // Validate payment status
        if ($body['paymentStatus'] !== "PAID") {
            Log::error('Invalid Monnify payment status.', ['paymentStatus' => $body['paymentStatus']]);
            return response()->json([
                'status' => 403,
                'message' => 'Payment status is not Paid'
            ], 403);
        }

        // Convert settlementAmount to float
        $settlementAmount = (float) $body['settlementAmount'];
        $amountPaid = (float) $body['amountPaid'];

        // Calculate new balance
        $oldBalance = $user->balance;
        // $newBalance = $oldBalance + $settlementAmount;
        $newBalance = $oldBalance + $amountPaid;

        // Use a database transaction to ensure atomicity
        DB::beginTransaction();
        try {
            // Update user balance
            $user->balance = $newBalance;
            $user->save();

            // Generate transaction reference
            $txf = $this->GenerateController->GenerateVirtualAccountReference();

            // Extract payment source information
            $paymentSource = $body['paymentSourceInformation'][0];
            $bankCode = $paymentSource['bankCode'];
            $accountName = $paymentSource['accountName'];
            $sessionId = $paymentSource['sessionId'];
            $accountNumber = $paymentSource['accountNumber'];

            // Create transaction record
            DB::table('transactions')->insertOrIgnore([
                'type' => "credit",
                'user_id' => $user->id,
                'txf' => $txf,
                'x_ref' => $body['transactionReference'],
                'balance_before' => $oldBalance,
                'balance_after' => $newBalance,
                'fee' => $amountPaid - $settlementAmount,
                'bonus' => $amountPaid - $settlementAmount,
                // 'amount' => $settlementAmount,
                'amount' => $amountPaid,
                'title' => "₦$amountPaid deposit",
                'trans_type' => 'funding',
                'amount_sent' => $amountPaid,
                'status' => "successful",
                'bank_code' => $bankCode,
                'account_name' => $accountName,
                'session_id' => $sessionId,
                'account_number' => $accountNumber,
                // 'narration' => "₦$settlementAmount deposit",
                'narration' => "₦$amountPaid deposit",
                'created_at' => now(),
            ]);

            // Create notification
            DB::table('notifications')->insertOrIgnore([
                'user_id' => $user->id,
                'ref' => $this->GenerateController->GenerateVirtualAccountReference(),
                'title' => "Successful Wallet Funding",
                'created_at' => now(),
                'img' => env("APP_URL") . "/funding_success.jpg",
                'message' => "Your transfer of $amountPaid was received and your balance has been successfully credited.",
            ]);

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            Log::error('Error processing Monnify webhook:', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }

        // Send email notifications
        try {
            Mail::to($user->email)->send(new GeneralMail("Your transfer of $amountPaid was received and your balance has been successfully credited.", $user->username, subject: "Credit Alert"));
            Mail::to(env("ADMIN_EMAIL"))->send(new GeneralMail("A customer account has been funded with the sum of $amountPaid. Email: $user->email", "ADMIN", subject: "Customer Wallet Funding"));
        } catch (\Throwable $th) {
            Log::error("Could not send an email to a customer who funded wallet.", ['error' => $th->getMessage()]);
        }

        // Send push notification
        try {
            $this->ApiController->SendPushNotification($user->device_id, "Credit Alert", "You have successfully received $amountPaid into your " . env("APP_NAME") . " account", ["data" => "test"]);
        } catch (\Throwable $th) {
            Log::error("Could not send push notification to user.", ['error' => $th->getMessage()]);
        }

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Webhook processed successfully'
        ], 200);
    }

    // Verify Monnify Transaction
    public function verifyMonnify( $ref ){

        // Get token from admin table
        $token = AdminModel::first();
        if (!$token) {
            return [
                'status' => false,
                'message' => "Authentication token not found"
            ];
        }

        $check = Http::withHeaders([
            'Authorization' => "Bearer $token->monnify_access_token",
            'Content-Type' => 'application/json',
        ])->get("https://api.monnify.com/api/v2/transactions/$ref");

        if (!$check->successful()) {
            Log::error('Failed to fetch Monnify transaction details.', ['response' => $check->json()]);
            return [
                'status' => false,
                'message' => "Failed to fetch transaction details"
            ];
        }
        // Log::info('Monnify transaction details verified and fetched successfully.', ['response' => $check->json()]);
        return [
            'status' => true,
            'message' => 'Transaction reference is valid',
            'data' => $check->json()
        ];


    }


}
