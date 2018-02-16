<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Socialite;
use GuzzleHttp\Client;
use App\Models\UserProvider;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('github')->user();

        // $user->token;
    }

    public function apiSocialLogin(Request $request) {
        $token = $request->get("access_token");
        $provider = $request->get("provider");
        if($provider === "vkontakte") {
            $client = new Client();
            $response = $client->get('https://api.vk.com/oauth/access_token?v=5.21&client_id=' .
                env('VKONTAKTE_KEY_MOBILE') .
                '&client_secret=' .
                env('VKONTAKTE_SECRET_MOBILE') .
                '&grant_type=client_credentials'
            );
            $access_token = json_decode($response->getBody()->getContents(), true)['access_token'];//todo it's BAD
            $response = $client->get('https://api.vk.com/method/secure.checkToken?token=' .
                $token . '&access_token=' . $access_token . '&client_secret=' .
                env('VKONTAKTE_SECRET_MOBILE').'&client_id=' .
                env('VKONTAKTE_KEY_MOBILE'));
            $response = json_decode($response->getBody()->getContents(), true)['response'];
            $email = $response['user_id'] . '-vk@memath.com';
            $providerUser = Socialite::driver($provider)->userFromToken([
                'user_id' => $response['user_id'],
                'email' => $email
            ]);

        }

        $account = UserProvider::whereProvider($provider)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if($account) {
            $newUser = $account->user;
        } else {
            $account = new UserProvider([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $provider
            ]);
            $user = User::whereEmail($email)->first();
            if(!$user) {
                $user = User::createBySocialProvider($providerUser, $email);
            }
            $account->user()->associate($user);
            $account->save();
            $newUser = $user;

        }
        $token = $newUser->createToken('SocialAuthToken')->accessToken;
        return [
            'success' => true,
            'user' => $newUser,
            'token' => $token
        ];
    }

}
