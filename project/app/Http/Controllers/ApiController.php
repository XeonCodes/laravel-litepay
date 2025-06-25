<?php

namespace App\Http\Controllers;

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

}
