<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class UserProvider extends Model
{
    const PROVIDER_VK = 'vkontakte';


    protected $table = 'user_providers';

    protected $fillable = [
        'provider', 'user_id', 'provider_user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
