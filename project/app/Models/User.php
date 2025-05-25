<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'gender',
        'email',
        'phone_number',
        'verification_otp',
        'otp_created_at',
        'email_v_status',
        'password',
        'bvn',
        'bvn_v_status',
        'balance',
        'balance_cashback',
        'last_claimed_at',
        'income',
        'expenses',
        'device_id',
        'pin',
        'token',
        'receive_transaction_emails',
        'receive_push_notifications',
        'weekly_newsletters',
        'account_type',
        'tier',
        'promo_code',
        'invited_by',
        'status',
        'last_transaction',
        'daily_bonus'
    ];

    // Append
    protected $append = ['notifications'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's username.
     *
     * @return string
     */
    protected function getUserName() {
        return strtoupper($this->username) . " For user";
    }


    // Get Username (Getter)
    public function getUsernameAttribute($value){
        return strtoupper($value);
    }

    // Mutate email to lowercase
    public function setEmailAttribute($value){
        $this->attributes['email'] = strtolower($value);
    }


    // Get notifications
    public function getNotificationsAttribute() {
        return $this->hasMany(NotificationModel::class, 'user_id', 'id')
            ->orderBy('created_at', 'desc');
    }

}
