<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\User;
use Carbon\Carbon;

use Socialite;
use Auth;
use Request;
use Image;

class LoginController extends FrontController
{
    public function facebook_login($locale=null) {

    	config(['services.facebook.redirect' => getLangUrl('login/callback/facebook')]);
        return Socialite::driver('facebook')->redirect();
    }

    public function twitter_login($locale=null) {

    	config(['services.twitter.redirect' => getLangUrl('login/callback/twitter')]);
        return Socialite::driver('twitter')->redirect();
    }

    public function gplus_login($locale=null) {

    	config(['services.google.redirect' => getLangUrl('login/callback/gplus')]);
        return Socialite::driver('google')->redirect();
    }


    public function facebook_callback() {
        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('login'));
        }
    	config(['services.facebook.redirect' => getLangUrl('login/callback/facebook')]);
        return $this->try_social_login(Socialite::driver('facebook')->user());
    }

    public function twitter_callback() {
    	config(['services.twitter.redirect' => getLangUrl('login/callback/twitter')]);
        return $this->try_social_login(Socialite::driver('twitter')->user());
    }

    public function gplus_callback() {
    	config(['services.google.redirect' => getLangUrl('login/callback/gplus')]);
        return $this->try_social_login(Socialite::driver('google')->user());
    }


    private function try_social_login($s_user) {

        $user = User::where( 'email','LIKE', $s_user->getEmail() )->first();

        if ($user) {
            Auth::login($user, true);

            Request::session()->flash('success-message', trans('front.page.registration.success'));
            return redirect('/');
        } else {
            Request::session()->flash('error-message', trans('front.page.registration.error'));
            return redirect( getLangUrl('login'));
        }
    }




    public function facebook_register($locale=null, $is_dentist) {
    	session(['is_dentist' => $is_dentist ]);
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);
        return Socialite::driver('facebook')->redirect();
    }

    public function twitter_register($locale=null, $is_dentist) {
    	session(['is_dentist' => $is_dentist ]);
        config(['services.twitter.redirect' => getLangUrl('register/callback/twitter') ]);
        return Socialite::driver('twitter')->redirect();
    }

    public function gplus_register($locale=null, $is_dentist) {
    	session(['is_dentist' => $is_dentist ]);
        config(['services.google.redirect' => getLangUrl('register/callback/gplus') ]);
        return Socialite::driver('google')->redirect();
    }


    public function facebook_callback_register() {
        
        config(['services.facebook.redirect' => getLangUrl('register/callback/facebook') ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('register') );
        }
        return $this->try_social_register(Socialite::driver('facebook')->user(), 'fb');
    }

    public function twitter_callback_register() {
        config(['services.twitter.redirect' => getLangUrl('register/callback/twitter') ]);

        // if (!Request::has('code') || Request::has('denied')) {
        //     dd('bla');
        //     return redirect('register');
        // }
        return $this->try_social_register(Socialite::driver('twitter')->user(), 'tw');
    }

    public function gplus_callback_register() {
        config(['services.google.redirect' => getLangUrl('register/callback/gplus') ]);

        if (!Request::has('code') || Request::has('denied')) {
            return redirect( getLangUrl('register') );
        }
        return $this->try_social_register(Socialite::driver('google')->user(), 'gp');
    }


    private function try_social_register($s_user, $network) {

        $is_dentist = session('is_dentist');
        $user = User::where( 'email','LIKE', $s_user->getEmail() )->first();

        if ($user) {
            Auth::login($user, true);

            Request::session()->flash('success-message', trans('front.page.registration.have-account'));
            return redirect('/');
        } else {
            $name = $s_user->getName() ? $s_user->getName() : explode('@', $s_user->getEmail() )[0];

            $password = $name.date('WY');
            $newuser = new User;
            $newuser->name = $name;
            $newuser->email = $s_user->getEmail();
            $newuser->password = bcrypt($password);
            $newuser->is_dentist = $is_dentist;
            $newuser->is_verified = true;
            $newuser->verified_on = Carbon::now();
            $newuser->country_id = $this->country_id;
            $newuser->city_id = $this->city_id;
            
            if(!empty(session('invited_by'))) {
                $newuser->invited_by = session('invited_by');
            }
            if(!empty(session('invite_secret'))) {
                $newuser->invite_secret = session('invite_secret');
            }
            
            $newuser->save();

            if(!$newuser->is_dentist) {                
                $avatarurl = $s_user->getAvatar();
                if($network=='fb') {
                    $avatarurl .= '&width=600&height=600';                
                } else if($network=='gp') {
                    $avatarurl = str_replace('sz=50', 'sz=600', $avatarurl);
                } else if($network=='tw') {
                    $avatarurl = str_replace('_normal', '', $avatarurl);
                }
                if(!empty($avatarurl)) {
                    $img = Image::make($avatarurl);
                    $newuser->addImage($img);
                }
            }

            $newuser->sendTemplate( $newuser->is_dentist ? 3 : 4 );

            Auth::login($newuser, true);

            if($newuser->invited_by) {
                Request::session()->flash('success-message', trans('front.page.registration.completed-by-invite', ['name' => $newuser->invitor->getName()]));
            } else {
                Request::session()->flash('success-message', trans('front.page.registration.completed'));
            }
            return redirect( $newuser->invitor->getLink() ? : getLangUrl('profile') );
        }
    }
}