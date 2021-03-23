<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\AppHelper;
use App\Controllers\AuthController;

class User extends Model {

    private $prefix = "";
    private $userinfo_key = [];
    private $auto_key_pass = "wlh_2019";

    public function __construct(){
        $this->prefix = env("DB_PREFIX");
        $this->userinfo_key = implode(',',[
            'id',
            'tentruycap',
            'keypass',
            'hoten',
            'email',
            'diachi',
            'sodienthoai',
            'gioitinh',
            'ngaysinh',
            'cmnd',
            'active',
            'phanquyen',
            'showhi',
            'icon',
            'id_facebook',
            'id_google',
            'google_icon',
        ]);
    }

    public static function find($id){
        $query = DB::select("SELECT * FROM `". env("DB_PREFIX") ."members` WHERE  `id`='" . $id . "' AND `phanquyen` = 0 LIMIT 1");
        if(!empty($query)){
            return $query[0];
        }
        return false;
    }

    public function get_user($id){
        $query = DB::select("SELECT ". $this->userinfo_key ." FROM `". $this->prefix ."members` WHERE  `id`='" . $id . "' AND `phanquyen` = 0 LIMIT 1");
        if(!empty($query)){
            return $query[0];
        }
        return false;
    }

    public function get_user_by_email($email){
        $query = DB::select("SELECT ". $this->userinfo_key ." FROM `". $this->prefix ."members` WHERE  `email`='" . $email . "' AND `phanquyen` = 0 LIMIT 1");
        if(!empty($query)){
            return $query[0];
        }
        return false;
    }

    public function get_user_by_gogoleID($googleID){
        $query = DB::select("SELECT ". $this->userinfo_key ." FROM `". $this->prefix ."members` WHERE  `id_google`='" . $googleID . "' AND `phanquyen` = 0 LIMIT 1");
        if(!empty($query)){
            return $query[0];
        }
        return false;
    }

    public function get_user_by_fbID($fbID){
        $query = DB::select("SELECT ". $this->userinfo_key ." FROM `". $this->prefix ."members` WHERE  `id_facebook`='" . $fbID . "' AND `phanquyen` = 0 LIMIT 1");
        if(!empty($query)){
            return $query[0];
        }
        return false;
    }

    public function email_exsist($email){
        $query = $this->get_user_by_email($email);
        if(!empty($query)){
            return true;
        }
        return false;
    }

    public function login_user($email, $pass, $phone_number = false){
        $userinfo_key_temp = $this->userinfo_key;
        $this->userinfo_key = $userinfo_key_temp.",matkhau";
        $query = $this->get_user_by_email($email);
        $this->userinfo_key = $userinfo_key_temp;
        if(!$query){
            return [
                'status' => 'error',
                'code' => 'email-not-exist',
                'message' => __('Email does not exist.')
            ];
        }
        if ($query->showhi != 1) {
            return [
                'status' => 'error',
                'code' => 'account-locked',
                'message' => __('Account locked!')
            ];
        }
        $pass = $this->create_pass($this->auto_key_pass . md5($this->auto_key_pass . $pass), $query->keypass);
        if ($pass != $query->matkhau) {
            return [
                'status' => 'error',
                'code' => 'email-password-wrong',
                'message' => __('Email or password is wrong!')
            ];
        }
        //Update date login
        $prefix = $this->prefix;
        DB::transaction(function () use($prefix,$query){
            DB::update("UPDATE `". $prefix ."members` SET `time_login` = '" . time() . "' WHERE `id` = '" . $query->id . "' LIMIT 1 ");
        }, 5);

        return [
            'status' => 'ok',
            'user_id' => $query->id
        ];
    }

    public function create_user($user_data){
        if (isset($user_data['email'])) {
            $email = $user_data['email'];
            $hoten = $user_data['fullname'];
            $sodienthoai = isset($user_data['phone']) ? $user_data['phone'] : "";
            $diachi = isset($user_data['address']) ? $user_data['address'] : "";
            $keypass = AppHelper::RANDOM_chuoi(5);
            $matkhau = $this->create_pass($this->auto_key_pass . md5($this->auto_key_pass . addslashes($user_data['password'])), $keypass);
            if($this->email_exsist($email)){
                return [
                    'status' => 'error',
                    'code' => 'email-exist',
                    'message' => __('Email does exist!')
                ];
            }
            $data = array();
            $data['tentruycap'] = AppHelper::STRIP_tag_text(str_replace(strstr($email, '@'), '', $email) . (rand(999, 9999)) . time());
            $data['email'] = AppHelper::STRIP_tag_text($email);
            $data['hoten'] = AppHelper::STRIP_tag_text($hoten);
            $data['sodienthoai'] = AppHelper::STRIP_tag_text($sodienthoai);
            $data['keypass'] = AppHelper::STRIP_tag_text($keypass);
            $data['matkhau'] = AppHelper::STRIP_tag_text($matkhau);
            $data['diachi'] = AppHelper::STRIP_tag_text($diachi);
            $data['gioitinh'] = isset($user_data['gioi_tinh']) ? $user_data['gioi_tinh'] : 0;
            $data['ngaysinh'] = isset($user_data['nam_sinh']) ? $user_data['nam_sinh'] : "";
            $data['cmnd'] = isset($user_data['cmnd']) ? $user_data['cmnd'] : "";
            $data['phanquyen'] = 0;
            $data['showhi'] = 1;
            if(isset($user_data['id_google'])){
                $data['id_google'] = $user_data['id_google'];
            }
            if(isset($user_data['id_facebook'])){
                $data['id_facebook'] = $user_data['id_facebook'];
            }
            if(isset($user_data['picture'])){
                $data['google_icon'] = $user_data['picture'];
            }
            $result = AppHelper::ACTION_db($data, 'members', 'add', array("themmoi"), NULL);
            if($result){
                $user = $this->login_user($email, $user_data['password']);
                $userdata = $this->get_user($user['user_id']);
                //unset($userdata->password);
                return [
                    'status' => 'ok',
                    'user_info' => $userdata
                ];
            } else {
                return [
                    'status' => 'error',
                    'code' => 'create-user-have-some-wrong',
                    'message' => __('Have some wrong please check log!')
                ];
            }

        }
    }

    public function create_pass($pass, $key){
        return strtoupper(md5($pass . md5($key)) . sha1($key . sha1($pass)));
    }
}
