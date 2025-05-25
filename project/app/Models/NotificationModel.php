<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationModel extends Model
{
    
    protected $table = "notifications";

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'status',
        'link',
        'img',
        'ref'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
