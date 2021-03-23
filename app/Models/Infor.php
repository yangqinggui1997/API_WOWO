<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\AppHelper;

class Infor extends Model
{
    public static function get_seo(){
        $prefix = env("DB_PREFIX");
        $sql_info = AppHelper::AOK("*", $prefix ."seo", "", "", 1, "", "where_clear");
        $info_seo = DB::select($sql_info);
        return $info_seo;
    }
}
