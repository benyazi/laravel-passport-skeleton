<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function createBySocialProvider($providerUser, $email = null)
    {
        if($email == null) {
            $email = $providerUser->getEmail();
        }
        $user =  self::create([
            'email' => $email,
            'name' => $providerUser->getName(),
            'password' => bcrypt(str_random()),
        ]);
        event(new Registered($user));
        return $user;
    }

    public function providers()
    {
        return $this->hasMany('App\Models\UserProvider', 'user_id');
    }

    public function hasProvider($providerName)
    {
        $providerModel = $this->providers()->where('provider',$providerName)->first();
        return (!empty($providerModel));
    }

    public function findForPassport($username) {
        return $this->where('phone', $username)->first();
    }

    public function delete() {
        $this->providers()->delete();
        parent::delete();
    }
}
