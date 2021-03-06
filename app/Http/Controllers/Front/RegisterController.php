<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\User;
use Carbon\Carbon;

use Validator;
use Auth;
use Request;
use Mail;

class RegisterController extends FrontController
{
    public function register($locale=null) {

        if(!empty($this->user)) {
            return redirect(getLangUrl('profile'));
        }

		return $this->ShowView('register', [
			'js' => [
				'register.js'
			],
		]);
    }

    public function register_form($locale=null) {

    	$validator = Validator::make(Request::all(), [
            'name' => array('required', 'min:3'),
            'email' => array('required', 'email', 'unique:users,email'),
            'is_dentist' => array('required', 'boolean'),
            'password' => array('required', 'min:6'),
    		'password-repeat' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return redirect(getLangUrl('register'))
            ->withInput()
            ->withErrors($validator);
        } else {
            
            $newuser = new User;
            $newuser->name = Request::input('name');
            $newuser->email = Request::input('email');
            $newuser->is_dentist = Request::input('is_dentist');
            $newuser->password = bcrypt(Request::input('password'));
            $newuser->country_id = $this->country_id;
            $newuser->city_id = $this->city_id;

            if(!empty(session('invited_by'))) {
                $newuser->invited_by = session('invited_by');
            }
            if(!empty(session('invite_secret'))) {
                $newuser->invite_secret = session('invite_secret');
            }

            $newuser->save();

            $newuser->sendTemplate( $newuser->is_dentist ? 1 : 2 );

            Auth::login($newuser, Request::input('remember'));

            if($newuser->invited_by) {
                Request::session()->flash('success-message', trans('front.page.registration.success-by-invite', ['name' => $newuser->invitor->getName()]  ));
            } else {
                Request::session()->flash('success-message', trans('front.page.registration.success'));
            }
            return redirect( $newuser->invitor->getLink() ? : getLangUrl('profile') );
        }
    }

    public function invite_accept($locale=null, $id, $hash, $secret) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_invite_token()) {

                session([
                    'invited_by' => $user->id,
                    'invite_secret' => $secret,
                ]);

                Request::session()->flash('success-message', trans('front.page.registration.invitation', [ 'name' => $user->name ]));
                return redirect( getLangUrl('register'));
            }
        }
        else {
            return redirect('/');
        }
    }

    public function register_verify($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {

                $user->verified_on = Carbon::now();
                $user->is_verified = true;

                $user->save();

                $user->sendTemplate( $user->is_dentist ? 3 : 4 );

                Auth::login($user, true);

                Request::session()->flash('success-message', trans('front.page.registration.profile-confirmed'));
                return redirect( getLangUrl('profile'));
            }
        }
        else {
            return redirect('/');
        }
    }

    public function forgot($locale=null) {

		return $this->ShowView('forgot-password');
    }

    public function forgot_form($locale=null) {

		$user = User::where([
            ['email','LIKE', Request::input('email') ]
        ])->first();

    	if(empty($user->id)) {
            Request::session()->flash('error-message', trans('front.page.registration.email-error'));
            return redirect( getLangUrl('forgot-password') );
        }

        $user->sendTemplate(5);

        Request::session()->flash('success-message', trans('front.page.registration.email-success'));
        return redirect( getLangUrl('forgot-password') );
    }

    public function recover($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {

                return $this->ShowView('recover-password', array(
                    'id' => $id,
                    'hash' => $hash,
                ));
            }
        }
        else {
            return redirect('');
        }
    }
    public function claim($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_invite_token()) {

                if(Request::isMethod('post')) {
                    $validator = Validator::make(Request::all(), [
                        'password' => array('required', 'min:6'),
                        'password-repeat' => 'required|same:password',
                    ]);

                    if ($validator->fails()) {
                        return redirect( getLangUrl('claim/'.$id.'/'.$hash))
                        ->withInput()
                        ->withErrors($validator);
                    } else {
                        
                        $user->is_verified = true;
                        $user->password = bcrypt(Request::input('password'));
                        $user->save();

                        Auth::login($user, true);

                        Request::session()->flash('success-message', trans('front.page.claim.success'));
                        return redirect( getLangUrl('profile') );
                    }

                }

                return $this->ShowView('claim', array(
                    'id' => $id,
                    'hash' => $hash,
                    'future_profile' => $user
                ));
            }
        }

        return redirect('/');
    }

    public function recover_form($locale=null, $id, $hash) {

        $user = User::find($id);

        if (!empty($user)) {

            if ($hash == $user->get_token()) {
                $validator = Validator::make(Request::all(), [
                    'password' => array('required', 'min:6'),
                    'password-repeat' => 'required|same:password',
                ]);

                if ($validator->fails()) {
                    return redirect( getLangUrl('recover/'.$id.'/'.$hash))
                    ->withInput()
                    ->withErrors($validator);
                } else {
                    
                    $user->password = bcrypt(Request::input('password'));
                    $user->save();

                    Request::session()->flash('success-message', trans('front.page.recover.success'));
                    return redirect( getLangUrl('login') );
                }
            }
        }
    }
}