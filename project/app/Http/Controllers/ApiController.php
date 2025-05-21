<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
}
