<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    private $prefix = "";
    private $endpoint;

    public function __construct(){
        $this->prefix = env("DB_PREFIX");
        $this->endpoint = env("ENDPOINT_URL");
    }

    public function getCategory($args = []){
        $default_args = [
            'id' => -1,
            'id_parent' => -1,
            'lang' => 'vi',
            'ramdom' => false,
            'order_by' => 'catasort',
            'order' => 'ASC'
        ];

        $args = array_merge($default_args, $args);
        $step = 2; //post type post
        $list_key = implode(',',[
            'id',
            'id_parent',
            'tenbaiviet_'. $args["lang"] .' as tendanhmuc',
            'seo_name',
            'icon',
            'step',
            'catasort',
            'tinhnang_loc',
            'tinhnang_loc_nhom',
            'gia_timkiem_den',
            'gia_timkiem_tu',
        ]);
        $where = ($args["id"] >= 1 ? "id = ".$args["id"] : "").($args["id"] >= 1 ? ($args['id_parent'] >= 0 ? " AND id_parent = ". $args['id_parent'] : "") : ($args['id_parent'] >= 0 ? " id_parent = ". $args['id_parent'] : ""));
        if($args['ramdom'])
            $sort = "RAND()";
        else
            $sort = " ". $args['order_by'] ." ". $args['order'];
        try
        {
            $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."danhmuc AS cat WHERE showhi = 1 AND step IN (".$step.") ". ($where ? " AND ".$where : $where) . " ORDER BY " . $sort);
            if(!$query)
                return false;
            $data = [];
            foreach($query as $key => $cat){
                $data[] = [
                    'id' => $cat->id,
                    'id_parent' => $cat->id_parent,
                    'tendanhmuc' => $cat->tendanhmuc,
                    'seo_name' => $cat->seo_name,
                    'icon' => (!empty($cat->icon)) ? $this->endpoint.'datafiles/setone/'.$cat->icon : "",
                    'step' => $cat->step,
                    'catasort' => $cat->catasort,
                    'tinhnang_loc' => $cat->tinhnang_loc,
                    'tinhnang_loc_nhom' => $cat->tinhnang_loc_nhom,
                    'gia_timkiem_den' => $cat->tinhnang_loc_nhom,
                    'gia_timkiem_tu' => $cat->gia_timkiem_tu
                ];
            }
            return $data;
        }
        catch (\Throwable $th)
        {
            //throw $th;
            //print_r($th->getMessage());
            return false;
        }
    }

    public function getFeature($filterFeature, $id, $k)
    {
        $obj = DB::select("SELECT NULL as childs, `tenbaiviet_vi`, `tenbaiviet_en`, `id`, `id_parent`, `duongdantin`, `icon` FROM ".$this->prefix."loaitin WHERE showhi = 1 AND id = ".$id." LIMIT 1");
        $childs = [];
        $_obj = NULL;
        for($i = $k + 1; $i < count($filterFeature); $i++)
        {
            if($filterFeature[$i] == 21 || $filterFeature[$i] == 20) break;
            $_obj = DB::select("SELECT NULL as childs, `tenbaiviet_vi`, `tenbaiviet_en`, `id`, `id_parent`, `duongdantin`, `icon` FROM ".$this->prefix."loaitin WHERE showhi = 1 AND id = ".$filterFeature[$i]." LIMIT 1");
            if($_obj)
                $childs[] = $_obj[0];
        }
        if($obj)
        {
            $obj[0]->childs = $childs;
            return $obj[0];
        }
        return NULL;
    }

    public function getPropertiesForFilter($args = []){
        try
        {
            $list_key = implode(',',[
                'id',
                'tinhnang_loc',
                'tinhnang_loc_nhom',
                'gia_timkiem_den',
                'gia_timkiem_tu',
            ]);
            $category = DB::select("SELECT ".$list_key." FROM lh_danhmuc WHERE showhi = 1 AND id = ? LIMIT 1", [$args['id']]);
            if(!$category)
                return false;
            $filterFeature = !empty($category[0]->tinhnang_loc) ? explode(",", $category[0]->tinhnang_loc) : array();
            $filterFeatureGroup = !empty($category[0]->tinhnang_loc_nhom) ? json_decode($category[0]->tinhnang_loc_nhom, true) : array();
            $listOfFeature = implode(',', array_keys($filterFeatureGroup));
            $feature = DB::select("SELECT NULL as type, NULL as min, NULL as max, `tenbaiviet_vi`, `tenbaiviet_en`, `id`, `id_parent`, `duongdantin`, `icon` ,`id_chon`  FROM ".$this->prefix."baiviet_tinhnang WHERE showhi = 1 AND id IN (".$listOfFeature.") ORDER BY `catasort` ASC", [$args['id']]);

            $listOfFea = [];
            foreach($feature as $f)
            {
                $fol = explode('_', $filterFeatureGroup[$f->id]);
                if($fol[0] == 0)
                {
                    if($f->id_chon != 0) continue;
                    $listOfFea[] = $f;
                }
                else
                {
                    $f->min = $fol[1];
                    $f->max = $fol[2];
                    $listOfFea[] = $f;
                }
            }
            $listOfOther = [];
            foreach($filterFeature as $k => $id)
            {
                switch($id)
                {
                    case 22:
                        $listOfOther[] = $this->getFeature($filterFeature, $id, $k);
                        break;
                    case 21:
                        $listOfOther[] = $this->getFeature($filterFeature, $id, $k);
                        break;
                    case 20:
                        $listOfOther[] = $this->getFeature($filterFeature, $id, $k);
                        break;
                    default:
                        break;
                }
            }
            $listOfProp = new \stdClass();
            $listOfProp->features = $listOfFea;
            $listOfProp->others = $listOfOther;
            return $listOfProp;
        }
        catch (\Throwable $th)
        {
            //throw $th;
            // return $th->getMessage();
            return false;
        }
    }
}
