<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    private $prefix = "";

    public function __construct(){
        $this->prefix = env("DB_PREFIX");
        $this->endpoint = env("ENDPOINT_URL");
    }

    public function get($args = [])
    {
        $default_args = [
            'id' => -1,
            'id_parent' => -1,
            'id_post'  => -1,
            'page'     => 1,
            'comments_per_page'  => 6,
        ];

        $args = array_merge($default_args, $args);
        $list_key = implode(',',[
            'cment.id',
            'cment.id_sp',
            'cment.ip_gui',
            'cment.uid',
            'cment.tenbaiviet_vi',
            'cment.noidung_vi',
            'cment.loai_binhluan',
            'cment.showhi',
            'cment.ngay_dang',
            'cment.id_parent',
            'user.hoten',
            'user.icon',
        ]);
        $start = ((int)$args['page']*(int)$args['comments_per_page']) - (int)$args['comments_per_page'];
        $numview = (int)$args['comments_per_page'];
        if($args['id_post'] >= 1 && $args['id_parent'] >= 0 && $args['id'] >= 1)
            $query_string = "SELECT ".$list_key." FROM ".$this->prefix."binhluan AS cment LEFT JOIN ".$this->prefix."members AS user ON cment.uid = user.id WHERE cment.showhi = 1 AND cment.id = ".$args['id']." AND cment.id_sp = ".$args['id_post']." AND cment.id_parent = ".$args['id_parent']. " ORDER BY cment.id ASC";
        else if ($args['id_post'] >= 1 && $args['id_parent'] >= 0)
            $query_string = "SELECT ".$list_key." FROM ".$this->prefix."binhluan AS cment LEFT JOIN ".$this->prefix."members AS user ON cment.uid = user.id WHERE cment.showhi = 1 AND cment.id_sp = ".$args['id_post']." AND cment.id_parent = ".$args['id_parent']. " ORDER BY cment.id ASC";
        else if ($args['id_post'] >= 1 && $args['id'] >= 1)
            $query_string = "SELECT ".$list_key." FROM ".$this->prefix."binhluan AS cment LEFT JOIN ".$this->prefix."members AS user ON cment.uid = user.id WHERE cment.showhi = 1 AND cment.id = ".$args['id']." AND cment.id_sp = ".$args['id_post']." ORDER BY cment.id ASC";
        else if($args['id_parent'] >= 0 && $args['id'] >= 1)
            $query_string = "SELECT ".$list_key." FROM ".$this->prefix."binhluan AS cment LEFT JOIN ".$this->prefix."members AS user ON cment.uid = user.id WHERE cment.showhi = 1 AND cment.id = ".$args['id']." AND cment.id_parent = ".$args['id_parent']. " ORDER BY cment.id ASC";
        else if($args['id_post'] >= 1)
            $query_string = "SELECT ".$list_key." FROM (SELECT a.* FROM ((SELECT a.id as sort, a.* FROM ".$this->prefix."binhluan AS a WHERE a.id_sp = ".$args['id_post']." AND a.id_parent = 0 AND a.showhi = 1 ORDER BY a.id ASC) UNION ALL (SELECT a.id_parent AS sort, a.* FROM ".$this->prefix."binhluan AS a WHERE a.id_sp = ".$args['id_post']." AND a.id_parent <> 0 AND a.showhi = 1 ORDER BY a.id_parent ASC)) AS a ORDER BY a.sort ASC) AS cment LEFT JOIN ".$this->prefix."members AS user ON cment.uid = user.id ORDER BY cment.sort ASC";
        else if($args['id'] >= 1)
            $query_string = "SELECT ".$list_key." FROM ".$this->prefix."binhluan AS cment LEFT JOIN ".$this->prefix."members AS user ON cment.uid = user.id WHERE cment.showhi = 1 AND cment.id = ".$args['id']." ORDER BY cment.id ASC";
        else if($args['id_parent'] >= 0)
            $query_string = "SELECT ".$list_key." FROM ".$this->prefix."binhluan AS cment LEFT JOIN ".$this->prefix."members AS user ON cment.uid = user.id WHERE cment.showhi = 1 AND cment.id_parent = ".$args['id_parent']." ORDER BY cment.id ASC";
        try
        {
            $data = [];
            $query = DB::select($query_string." LIMIT ".$start.", ".$numview);
            if(!$query)
                return $data;
            foreach($query as $bl){
                $obj = new \stdClass();
                $obj->id = $bl->id;
                $obj->id_post = $bl->id_sp;
                $obj->ip_sent = $bl->ip_gui;
                $obj->title_of_post = $bl->tenbaiviet_vi;
                $obj->content = $bl->noidung_vi;
                $obj->type_of_comment = $bl->loai_binhluan;
                $obj->showhi = $bl->showhi;
                $obj->date_of_post = $bl->ngay_dang;
                $obj->id_parent = $bl->id_parent;
                $obj->user = [
                        'id' => $bl->uid,
                        'name' => $bl->hoten,
                        'icon' => $bl->icon ? $this->endpoint."datafiles/member/".$bl->uid."/".$bl->icon : "images/user_thumb.png"
                ];
                $data[] = $obj;
            }

            return $data;
        }
        catch(\Throwable $thr)
        {
            // return $thr->getMessage();
            return false;
        }
    }

    public function countComment($args = []){
        $default_args = [
            'id' => -1,
            'id_parent' => -1,
            'id_post'  => -1,
        ];
        $args = array_merge($default_args, $args);

        $query = [];
        $id = ($args["id"] >= 1 ? " AND id = ".$args["id"] : "");
        $id_parent = (($args["id_parent"] >= 0 && !is_null($args["id_parent"])) ? " AND id_parent = ".$args["id_parent"] : "");
        $id_post = ($args["id_post"] >= 1 ? " AND id_sp = ".$args["id_post"] : "");
        $where = $id.$id_parent.$id_post;
        try
        {
            $query = DB::select("SELECT id FROM `". $this->prefix ."binhluan` WHERE `showhi` =  1".$where);
            return count($query);
        }
        catch(\Throwable $thr)
        {
            return false;
            // return $thr->getMessage();
        }
    }

    public function create($fields)
    {
        $query = false;
        $keys = array_keys($fields);
        $keys = implode(",",$keys);
        $values = array_values($fields);
        $values = implode(',',$values);
        try
        {
            DB::transaction(function () use (&$query, $keys, $values){
                    $query = DB::insert("INSERT INTO ".$this->prefix."binhluan(".$keys.") VALUES (".$values.")");
            }, 5);
            if(!$query){
                return false;
            }
            return $query;
        }
        catch(\Throwable $thr)
        {
            // return $thr->getMessage();
            return false;
        }
    }
}
