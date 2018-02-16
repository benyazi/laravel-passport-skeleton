<?php

namespace App\Services;

use App\Models\UserProvider;
use App\User;
use Illuminate\Support\Facades\Auth;

class SocialAccountService
{
    public $errors = [];
    public function createOrGetUser($providerObj, $providerName)
    {
        $providerUser = $providerObj->user();

        $account = UserProvider::whereProvider($providerName)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            $this->updateUserAvatar($providerUser, $account->user);
            return $account->user;
        } else {
            $account = new UserProvider([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $providerName
            ]);

            if($providerUser->getEmail() == null || empty($providerUser->getEmail())) {
                $email = $providerUser->getId().'@' .$providerName. '.dev';//todo temp email vk
            } else {
                $email = $providerUser->getEmail();
            }

            $user = User::whereEmail($email)->first();//TODO this is very BAD!!!

            if (!$user) {
                $user = User::createBySocialProvider($providerUser, $email);
            }

            $account->user()->associate($user);
            $account->save();
            //$this->updateUserAvatar($providerUser, $account->user);

            return $user;
        }
    }

    public function addProviderToUser($providerObj, $providerName)
    {
        $providerUser = $providerObj->user();

        $account = UserProvider::whereProvider($providerName)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            $this->errors[] = 'Этот аккаунт социальной сети уже привязан в другом профиле.';
            return false;
        } else {
            $account = new UserProvider([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $providerName
            ]);
            $user = Auth::user();
            $account->user()->associate($user);
            $account->save();
            //$this->updateUserAvatar($providerUser, $account->user);
            return $user;
        }
    }

    static public function updateUserAvatar($providerUser, $user)
    {
        $fileName = 'UserAvatar_' . $user->id;
        /**
         * @var $user User
         */
        $avatar = $providerUser->getAvatar();
        if ($avatar == null) {
            return;
        }
        $mediaItems = $user->getMedia('avatar');
        foreach ($mediaItems as $mediaItem) {
            //$mediaItem->delete();
        }
        try {
            $user->addMediaFromUrl($avatar)
                ->usingName($fileName)
                ->toMediaCollection('avatar', 'tmp_media');
        } catch (\Exception $e) {

        }
    }
}