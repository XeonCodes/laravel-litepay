<?php

namespace App\Http\Controllers;

use App\Models\VirtualAccountModel;
use Illuminate\Http\Request;

class GenerateController extends Controller
{
    
    // Generate 6 digit otp
    public function GenerateOtp(){
        $otp = rand(100000, 999999);
        return $otp;
    }

    // Generate promo code
    public function GeneratePromoCode($length = 6){
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $promoCode = '';
        for ($i = 0; $i < $length; $i++) {
            $promoCode .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $promoCode;
    }

    // Generate Virtual Account Reference
    public function GenerateVirtualAccountReference()
    {
        do {
            $ref = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 15);
        } while (VirtualAccountModel::where('account_reference', $ref)->exists());
        return $ref;
    }

}
