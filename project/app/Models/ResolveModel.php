<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResolveModel extends Model
{

    use HasFactory;

    protected $table = 'resolve_transactions';

    protected $fillable = [
        'user_id',
        'txf',
        'amount',
        'fee',
        'session_id',
        'trans_type',
        'account_type',
        'beneficiary',
        'merchant',
        'product',
        'status',
        'narration',
        'sender',
        'bonus',
        'account_name',
        'account_number',
        'bank_name',
        'bank_code'
    ];

    // Relationship
    public function User(){
        return $this->belongsTo(User::class);
    }

}
