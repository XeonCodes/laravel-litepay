<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminModel extends Model
{
    
    protected $table = 'admin';

    protected $fillable = [
        'monnify_access_token',
        'monnify_access_token_lasttime'
    ];

}
