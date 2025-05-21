<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualAccountModel extends Model
{
    //

    protected $table = 'virtual_accounts';

    protected $fillable = [
        'user_id',
        'account_number',
        'bank_name',
        'bank_code',
        'account_type',
        'status',
        'balance'
    ];

    // User relationship
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }

}
