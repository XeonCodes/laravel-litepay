<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{

    // Buy Airtime
    public function BuyAirtimeClubkon($network, $amount, $phone_number, $ref){

        // Validate the request
        try {
            
            $apiCall = Http::withHeaders([
                "Authorization" => "Token apiKey",
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post("https://www.nellobytesystems.com/APIAirtimeV1.asp", [
                "UserID" => "your_userid",
                "APIKey" => "your_apikey",
                "MobileNetwork" => "mobilenetwork_code",
                "Amount" => "order_amount",
                "MobileNumber" => "recipient_mobilenumber",
                "&RequestID" => "request_id",
                "CallBackURL" => "https://liteapi.radiustech.com.ng/v1/callback"
            ]);

            if($apiCall->successful()){
            }

        } catch (\Throwable $th) {
            //throw $th;
        }

    }

    // Send Push Notification
    public function SendPushNotification ($to, $title, $body, $data) {

        try {

            $response = Http::withHeaders([
                "Content-Type" => "application/json"
            ])->post('https://exp.host/--/api/v2/push/send', [
                'to' => $to,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);

            $response->throw();

            Log::info($response);

            return [
                'status' => true,
                'message' => 'Push notification sent successfully'
            ];

        } catch (\Throwable $th) {
            Log::error('Error sending push notification: '. $th->getMessage());
            return [
            'status' => false,
            'message' => 'Failed to send push notification'
            ];
        }

    }



    // Buy Data (1)
    public function BuyDataStation($phone_number, $network, $plan)
    {

        $network_used = match ($network) {
            "MTN" => "1",
            "GLO" => "2",
            "Glo" => "2",
            "AIRTEL" => "4",
            "Airtel" => "4",
            "9MOBILE" => "3",
            "9Mobile" => "3",
            "9mobile" => "3"
        };

        Log::info("Network: $network");

        $buy = Http::withHeaders([
            'Authorization' => 'Token ' . env("DATA_STATION_KEY"),
            'Content-Type' => 'application/json'
        ])->post("https://datastationapi.com/api/data/", [
                    'network' => $network_used,
                    'mobile_number' => $phone_number,
                    'plan' => $plan,
                    'Ported_number' => true,
                ]);

        $jsonBuy = $buy->json();

        Log::info($jsonBuy);

        if (isset($jsonBuy['Status']) && $jsonBuy['Status'] === 'successful') {
            return [
                "status" => true,
                "amount" => $jsonBuy['plan_amount'],
                "x_ref" => $jsonBuy['ident'],
                "product_name" => "data"
            ];
        } else {
            $errorObj = (object) $jsonBuy;
            return [
                "status" => false,
                "message" => "Something went wrong. Try again",
                "error" => json_encode($jsonBuy),
                "error_msg" => $errorObj
            ];
        }


        // array(
        //     "id" => 16752903,
        //     "ident" => "Data921167495-cb2",
        //     "customer_ref" => "",
        //     "network" => 1,
        //     "balance_before" => "7433.030000000004",
        //     "balance_after" => "7383.030000000004",
        //     "mobile_number" => "08141314105",
        //     "plan" => 260,
        //     "Status" => "successful",
        //     "api_response" => "Dear Customer, You have successfully shared 150MB Data to 2348141314105. Your new Corporate Gifting data balance is 28305.13GB expires 28/07/2024. Thank you.",
        //     "plan_network" => "MTN",
        //     "plan_name" => "150.0MB",
        //     "plan_amount" => "50.0",
        //     "create_date" => "2024-06-22T05:08:24.505338",
        //     "Ported_number" => true
        // );



    }

    // Buy Airtime
    public function BuyDataClubKon($DataPlan, $phone_number, $network_code, $ref)
    {
        try {
            
            $Buy = Http::get('https://www.nellobytesystems.com/APIDatabundleV1.asp', [
                'UserID' => env('CLUB_KON_ID'),
                'APIKey' => env('CLUB_KON'),
                'MobileNetwork' => $network_code,
                'DataPlan' => $DataPlan,
                'MobileNumber' => $phone_number,
                'RequestID' => $ref,
                'CallBackURL' => env("APP_URL") . "/v1/callback/clubkon_data"
            ]);

            $BuyJson = $Buy->json();
            
            Log::info($BuyJson);

            if ($BuyJson['status'] != "ORDER_RECEIVED") {
                return [
                    "status" => false,
                    "message" => $BuyJson['status']
                ];
            } else {
                return [
                    "status" => true,
                    "amount" => $BuyJson['amount'],
                    "x_ref" => $BuyJson['orderid'],
                    "response" => $BuyJson['status'],
                    "message" => "Airtime purchase processed successfully"
                ];
            }
        } catch (Exception $th) {
            return [
                "status" => false,
                "error" => $th->getMessage()
            ];
        }
    }

    // Buy Data (2)
    public function BuyDataGladT($phone_number, $network, $plan, $ident)
    {

        $network_used = match ($network) {
            "MTN" => "1",
            "GLO" => "2",
            "Glo" => "2",
            "AIRTEL" => "3",
            "Airtel" => "3",
            "9MOBILE" => "6",
            "9Mobile" => "6",
            "9mobile" => "6"
        };

        Log::info("Network: $network");

        $buy = Http::withHeaders([
            'Authorization' => 'Token ' . env("GLAD_T_KEY"),
            'Content-Type' => 'application/json'
        ])->post("https://www.gladtidingsdata.com/api/data/", [
                    'network' => $network_used,
                    'mobile_number' => $phone_number,
                    'plan' => $plan,
                    'Ported_number' => true,
                    "ident" => $ident
                ]);

        $jsonBuy = $buy->json();

        Log::info($jsonBuy);

        if (isset($jsonBuy['Status']) && $jsonBuy['Status'] === 'successful') {
            return [
                "status" => true,
                "amount" => $jsonBuy['plan_amount'],
                "x_ref" => $jsonBuy['ident'],
                "product_name" => "data"
            ];
        } else {
            $errorObj = (object) $jsonBuy;
            return [
                "status" => false,
                "message" => "Something went wrong. Try again",
                "error" => json_encode($jsonBuy),
                "error_msg" => $errorObj
            ];
        }


        // array(
        //     "id" => 16752903,
        //     "ident" => "Data921167495-cb2",
        //     "customer_ref" => "",
        //     "network" => 1,
        //     "balance_before" => "7433.030000000004",
        //     "balance_after" => "7383.030000000004",
        //     "mobile_number" => "08141314105",
        //     "plan" => 260,
        //     "Status" => "successful",
        //     "api_response" => "Dear Customer, You have successfully shared 150MB Data to 2348141314105. Your new Corporate Gifting data balance is 28305.13GB expires 28/07/2024. Thank you.",
        //     "plan_network" => "MTN",
        //     "plan_name" => "150.0MB",
        //     "plan_amount" => "50.0",
        //     "create_date" => "2024-06-22T05:08:24.505338",
        //     "Ported_number" => true
        // );



    }

    // Buy Data (2)
    public function BuyDataMostCheap($phone_number, $network, $plan, $ident)
    {

        $network_used = match ($network) {
            "MTN" => "1",
            "GLO" => "2",
            "Glo" => "2",
            "AIRTEL" => "4",
            "Airtel" => "4",
            "9MOBILE" => "3",
            "9Mobile" => "3",
            "9mobile" => "3"
        };

        $buy = Http::withHeaders([
            'Authorization' => 'Token ' . env("MOST_KEY"),
            'Content-Type' => 'application/json'
        ])->post("https://mostcheapestdata.com/api/data/", [
                    'network' => $network_used,
                    'mobile_number' => $phone_number,
                    'plan' => $plan,
                    'Ported_number' => true,
                    // "ident" => $ident
                ]);

        $jsonBuy = $buy->json();

        Log::info($jsonBuy);

        if (isset($jsonBuy['Status']) && $jsonBuy['Status'] === 'successful') {
            return [
                "status" => true,
                "amount" => $jsonBuy['plan_amount'],
                "x_ref" => $jsonBuy['ident'],
                "product_name" => "data"
            ];
        } else {
            $errorObj = (object) $jsonBuy;
            return [
                "status" => false,
                "message" => "Something went wrong. Try again",
                "error" => json_encode($jsonBuy),
                "error_msg" => $errorObj
            ];
            
        }


        // array(
        //     "id" => 16752903,
        //     "ident" => "Data921167495-cb2",
        //     "customer_ref" => "",
        //     "network" => 1,
        //     "balance_before" => "7433.030000000004",
        //     "balance_after" => "7383.030000000004",
        //     "mobile_number" => "08141314105",
        //     "plan" => 260,
        //     "Status" => "successful",
        //     "api_response" => "Dear Customer, You have successfully shared 150MB Data to 2348141314105. Your new Corporate Gifting data balance is 28305.13GB expires 28/07/2024. Thank you.",
        //     "plan_network" => "MTN",
        //     "plan_name" => "150.0MB",
        //     "plan_amount" => "50.0",
        //     "create_date" => "2024-06-22T05:08:24.505338",
        //     "Ported_number" => true
        // );



    }


}
