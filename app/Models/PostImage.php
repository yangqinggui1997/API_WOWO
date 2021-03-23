<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    private $prefix = "";

    public function __construct(){
        $this->prefix = env("DB_PREFIX");
    }


    public function getPostImage($params = [])
    {
        $default_args = [
            'id_post' => ''
        ];
        $args = array_merge($default_args, $params);
        $list_key = implode(',',[
            'id',
            'icon',
            'duongdantin AS directory_of_news',
        ]);
        $where = "";
        if(!empty($args['id_post']))
            $where = " id_parent = ". $args['id_post'];
        try
        {
            $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."baiviet_img ". ($where ? "WHERE  ".$where : "")." ORDER BY sort ASC");
            if(!$query)
                return false;
            return $query;
        }
        catch(\Throwable $thr)
        {
            // return $thr->getMessage();
            return false;
        }
    }
}
