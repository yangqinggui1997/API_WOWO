<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use App\Mail\NewUser;
use App\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Routing\Controller as BaseController;

class UserController extends BaseController {
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request) 
    {
        $this->request = $request;
    }

    public function register_user()
    {
        $validator = Validator::make($this->request->all(), [
            'email'     => 'required|email',
            'password'  => 'required',
            'fullname'  => 'required',
            'phone'  => 'required',
            'address'  => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        }

        // Check the user
        $user = new User();
        $user_data['email'] = $this->request->input('email');
        $user_data['password'] = $this->request->input('password');
        $user_data['fullname'] = $this->request->input('fullname');
        $user_data['phone'] = $this->request->input('phone');
        $user_data['address'] = $this->request->input('address');
        $userdata = $user->create_user($user_data); 
        //Send Email
        Mail::to($user_data['email'])->send(new NewUser($userdata));
        return response()->json($userdata, 200);
    }

    public function register_user_by_social($service = 'google', $social_id, $avatar)
    {
        $user = new User();
        $user_data['email'] = $this->request->input('email');
        $user_data['password'] = AppHelper::RANDOM_chuoi(5);
        $user_data['fullname'] = $this->request->input('fullname');
        switch ($service) {
            case 'google':
                $user_data['id_google'] = $social_id;
                $user_data['picture'] = $avatar;
                break;
            case 'facebook':
                $user_data['id_facebook'] = $social_id;
                $user_data['picture'] = $avatar;
                break;
            default:
                return false;
                break;
        }
        $userdata = $user->create_user($user_data);
        return $userdata;
    }

    public function me()
    {
        $user = $this->request->auth;
        if(!$user){
            return response()->json([
                'status' => 'error',
                'code' => 'you-need-login',
                'message' => __('you need login!')
            ], 200);
        }
        $id = $user->id;
        $user = new User();
        $userdata = $user->get_user($id);
        return response()->json([
            'status' => 'ok',
            'user_info' => $userdata
        ], 200);
    }

    public function update()
    {
        $user = $this->request->auth;
        if(!$user){
            return response()->json([
                'status' => 'error',
                'code' => 'you-need-login',
                'message' => __('you need login!')
            ], 200);
        }
        $user_id = $user->id;
        return response()->json([
            'status' => 'ok',

        ], 200);
    }

    public function logout()
    {
        $user = $this->request->auth;
        if(!$user){
            return response()->json([
                'status' => 'error',
                'code' => 'you-need-login',
                'message' => __('User no login!')
            ], 200);
        }
        return response()->json([
            'status' => 'ok'
        ], 200);
    }
}