<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\SocialProfile;

class Socialitelogincontroller extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    private $socialiteDrivers = ['google','facebook'];

    public function login($driver)
    {
        if (in_array($driver, $this->socialiteDrivers)) {
            return Socialite::driver($driver)->redirect();
        }
        else{
            return redirect()->route('login');
        }
        
    }

    public function callback(Request $request, $driver)
    {
        if ($request->get('error')) {
            return redirect()->route('login');
        }

        $userSocialite = Socialite::driver($driver)->user();
        
        $social_profile = SocialProfile::where('social_id', $userSocialite->getId())
                                        ->where('social_name', $driver)->first();


        if(!$social_profile){

            $user = User::where('email', $userSocialite->getEmail())->first();

            if(!$user){
                $user = User::create([
                    'name' => $userSocialite->getName(),
                    'email'=> $userSocialite->getEmail(),
                ]);
            }


            $social_profile = SocialProfile::create([
                'user_id' => $user->id,
                'social_id' => $userSocialite->getId(),
                'social_name' => $driver,
                'social_avatar'  => $userSocialite->getAvatar()
            ]);
        }

        auth()->login($social_profile->user);

        return redirect()->intended('dashboard');
    }
}
