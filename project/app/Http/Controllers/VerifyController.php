<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    // Verify User Promo Code
    public function VerifyPromoCode($promoCode){

        $check = User::where("promo_code", $promoCode)->first();
        if($check){
            return true;
        }else{
            return false;
        }

    }
}
