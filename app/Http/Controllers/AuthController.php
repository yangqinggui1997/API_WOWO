<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use Firebase\JWT\JWT;
use Google_Client;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController 
{
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

    protected function jwt($user_id) 
    {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user_id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 60*60 // Expiration time
        ];
        
        // As you can see we are passing `JWT_SECRET` as the second parameter that will 
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    } 

    protected function jwt_refresh($user_id) 
    {
        $payload = [
            'iss' => "lumen-jwt-refresh", // Issuer of the token
            'sub' => $user_id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 14*86400 // Expiration time
        ];
        
        // As you can see we are passing `JWT_SECRET` as the second parameter that will 
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET_REFRESH'));
    }

    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     * 
     * @param  \App\User   $user 
     * @return mixed
     */
    public function authenticate()
    {
        $validator = Validator::make($this->request->all(), [
            'email'     => 'required|email',
            'password'  => 'required'
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
        $user_login = $user->login_user($this->request->input('email'), $this->request->input('password'));

        // Verify and generate the token
        if ($user_login['status'] == "ok") {
            $userdata = $user->get_user($user_login['user_id']);
            unset($userdata->password);
            return response()->json([
                'status' => 'ok',
                'user_info' => $userdata,
                'token' => $this->jwt($user_login['user_id']),
                'refresh_token' => $this->jwt_refresh($user_login['user_id']),
            ], 200);
        } else {
            return response()->json($user_login, 200);
        }
    }

    public function refresh(){
        $validator = Validator::make($this->request->all(), [
            'refresh_token'     => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'code' => 'token-not-provided',
                'message' => __('Token not provided.')
            ], 200);
        }
        $refresh_token = $this->request->input('refresh_token');
        try {
            $credentials = JWT::decode($refresh_token, env('JWT_SECRET_REFRESH'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'refresh-token-expired',
                'message' => __('Provided refresh token is expired.')
            ], 200);
        } catch(\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'error-decoding-refresh-token',
                'message' => __('An error while decoding token refresh.')
            ], 200);
        }
        $user_id = $credentials->sub;
        return response()->json([
            'status' => 'ok',
            'token' => $this->jwt($user_id),
        ], 200);
    }

    public function authenticate_google(){
        $validator = Validator::make($this->request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'fullname' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        }

        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID') ]);
        try{
            $user_id = "";
            $google_user_id = "";
            $payload = $client->verifyIdToken( $_POST['access_token'] );
            if ( $payload ) {
                $google_user_id = $payload['sub'];
                $user = new User();
                $userdata = $user->get_id_by_googleID($google_user_id);
                if($userdata){
                    //Login
                    if($userdata->showhi != 1){
                        return response()->json([
                            'status' => 'error',
                            'code' => 'account-locked',
                            'message' => __('Account locked!')
                        ], 200);
                    } 
                    return response()->json([
                        'status' => 'ok',
                        'user_info' => $userdata,
                        'token' => $this->jwt($user_login['user_id']),
                        'refresh_token' => $this->jwt_refresh($user_login['user_id']),
                    ], 200);
                } else {
                    //Create new user
                    $result = $user->register_user_by_social('google', $google_user_id, $this->request->input['avatar']);
                    if(isset($result['status']) && $result['status'] == 'ok'){
                        return response()->json([
                            'status' => 'ok',
                            'user_info' => $result['user_info'],
                            'token' => $this->jwt($result['user_info']->id),
                            'refresh_token' => $this->jwt_refresh($result['user_info']->id),
                        ], 200);
                    } else {
                        return response()->json($result, 200);
                    }
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'code' => 'cannot-get-google-id',
                    'message' => __('Can\'t get google id')
                ], 200);
            }
        } catch ( \Throwable $e ) {
            return response()->json([
                'status' => 'error',
                'code' => 'have-wrong-login-with-google',
                'message' => $e->getMessage()
            ], 200); 
        }
    }

    public function authenticate_fb(){
        $validator = Validator::make($this->request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'fullname' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        }

        $fb = new Facebook([
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_SECRET_KEY'),
            'default_graph_version' => 'v8.0',
        ]);
        try{
            $response = $this->fb->get('/me', $this->access_token);
            if ( $response ) {
                $me = $response->getGraphUser();
                $fb_user_id = $me->getID();
                $user = new User();
                $userdata = $user->get_id_by_fbID($fb_user_id);
                if($userdata){
                    //Login
                    if($userdata->showhi != 1){
                        return response()->json([
                            'status' => 'error',
                            'code' => 'account-locked',
                            'message' => __('Account locked!')
                        ], 200);
                    }
                    return response()->json([
                        'status' => 'ok',
                        'user_info' => $userdata,
                        'token' => $this->jwt($user_login['user_id']),
                        'refresh_token' => $this->jwt_refresh($user_login['user_id']),
                    ], 200);
                } else {
                    //Create new user
                    $result = $user->register_user_by_social('facebook', $fb_user_id, $this->request->input['avatar']);
                    if(isset($result['status']) && $result['status'] == 'ok'){
                        return response()->json([
                            'status' => 'ok',
                            'user_info' => $result['user_info'],
                            'token' => $this->jwt($result['user_info']->id),
                            'refresh_token' => $this->jwt_refresh($result['user_info']->id),
                        ], 200);
                    } else {
                        return response()->json($result, 200);
                    }
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'code' => 'cannot-get-facebook-id',
                    'message' => __('Can\'t get facebook id')
                ], 200);
            }
        } catch ( \Throwable $e ) {
            return response()->json([
                'status' => 'error',
                'code' => 'have-wrong-login-with-facebook',
                'message' => $e->getMessage()
            ], 200); 
        }
    }
}
