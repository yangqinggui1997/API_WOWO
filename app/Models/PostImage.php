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
            'id_parent' => '',
            'order_by' => 'sort ASC',
        ];

        $args = array_merge($default_args, $params);
        $list_key = implode(',',[
            'id',
            'icon',
            'duongdantin',
        ]);
        $where = "";
        if(!empty($args['id_parent']))
            $where = " id_parent = ". $args['id_parent'];
        try
        {
            $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."baiviet_img ". ($where ? "WHERE  ".$where : "")." ORDER BY ".$args['order_by']);
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
