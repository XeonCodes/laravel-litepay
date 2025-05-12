<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Get user balance
    public function getUserBalance(Request $request){
        $userId = $request->input("user_id");
        $user = User::where("id", $userId)->first();
        return $user->balance;
    }
}
