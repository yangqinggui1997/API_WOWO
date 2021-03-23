<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    private $prefix = "";

    public function __construct(){
        $this->prefix = env("DB_PREFIX");
    }

    public function getLocation($args = [])
    {
        $default_args = [
            'lang' => 'vi',
            'id' => -1,
            'id_parent' => -1
        ];
        $args = array_merge($default_args, $args);
        $id = $args["id"];
        $id_parent = $args["id_parent"];
        $list_key = implode(',',[
            'id',
            'id_parent',
            'tenbaiviet_'.$args["lang"].' AS name',
            'seo_name',
        ]);
        //String Query
        try
        {
            //Query
            $query = false;
            if($id_parent >= 0 && !is_null($id_parent) && $id >= 1)
                $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."diadiem WHERE id_parent = ".$id_parent." AND id = ".$id." ORDER BY catasort");
            else if($id_parent >= 0 && !is_null($id_parent))
                $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."diadiem WHERE id_parent = ".$id_parent." ORDER BY catasort");
            else if($id >= 1)
                $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."diadiem WHERE id = ?", [$id]);
            else
                $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."diadiem WHERE showhi = 1 ORDER BY id_parent");

            if(!$query)
                return false;
            $data = [];
            foreach($query as $dd){
                $obj = new \stdClass();
                $obj->id = $dd->id;
                $obj->name = $dd->name;
                $obj->seo_name = $dd->seo_name;
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

}
