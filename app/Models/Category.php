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
            'order_by' => 'catasort',
            'order' => 'ASC',
            'lang' => 'vi'
        ];

        $args = array_merge($default_args, $args);
        $step = 2; //post type post
        $lang = (empty($args['lang']) || is_null($args['lang'])) ? 'vi': (($args['lang'] != 'en' || $args['lang'] != 'vi') ? 'vi' : $args['lang']);
        $list_key = implode(',',[
            'id',
            'id_parent',
            'tenbaiviet_'. $lang .' AS category_name',
            'seo_name',
            'icon',
            'step',
            'catasort',
            'tinhnang_loc AS feature_filter',
            'tinhnang_loc_nhom AS feature_filter_group',
            'gia_timkiem_den AS price_to',
            'gia_timkiem_tu AS price_from',
        ]);
        $where = ($args["id"] >= 1 ? "id = ".$args["id"] : "").($args["id"] >= 1 ? ($args['id_parent'] >= 0 ? " AND id_parent = ". $args['id_parent'] : "") : ($args['id_parent'] >= 0 ? " id_parent = ". $args['id_parent'] : ""));
        $sort = "";
        $order_by = explode(',', $args['order_by']);
        $order = explode(',', $args['order']);
        if(count($order_by) === count($order))
            foreach($order_by as $key => $value)
                $sort .= $sort ? ", ". $value ." ". $order[$key] : " ". $value ." ". $order[$key];
        else
            $sort = "catasort ASC";
        try
        {
            $data = [];
            $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."danhmuc WHERE showhi = 1 AND step IN (".$step.") ". ($where ? " AND ".$where : $where) . " ORDER BY " . $sort);
            if(!$query)
                return $data;
            foreach($query as $key => $cat){
                $data[] = [
                    'id' => $cat->id,
                    'id_parent' => $cat->id_parent,
                    'category_name' => $cat->category_name,
                    'seo_name' => $cat->seo_name,
                    'icon' => (!empty($cat->icon)) ? $this->endpoint.'datafiles/setone/'.$cat->icon : "",
                    'step' => $cat->step,
                    'catasort' => $cat->catasort,
                    'feature_filter' => $cat->feature_filter,
                    'feature_filter_group' => $cat->feature_filter_group,
                    'price_to' => $cat->price_to,
                    'price_from' => $cat->price_from
                ];
            }
            return $data;
        }
        catch (\Throwable $th)
        {
            // return $th->getMessage();
            return false;
        }
    }

    public function getFeature($filterFeature, $id, $k)
    {
        $obj = DB::select("SELECT NULL as childs, `tenbaiviet_vi` AS name_vi, `tenbaiviet_en` AS name_en, `id`, `id_parent`, `duongdantin` AS directory_news, `icon` FROM ".$this->prefix."loaitin WHERE showhi = 1 AND id = ".$id." LIMIT 1");
        $childs = [];
        $_obj = NULL;
        for($i = $k + 1; $i < count($filterFeature); $i++)
        {
            if($filterFeature[$i] == 21 || $filterFeature[$i] == 20) break;
            $_obj = DB::select("SELECT NULL as childs, `tenbaiviet_vi` AS name_vi, `tenbaiviet_en` AS name_en, `id`, `id_parent`, `duongdantin` AS directory_news, `icon` FROM ".$this->prefix."loaitin WHERE showhi = 1 AND id = ".$filterFeature[$i]." LIMIT 1");
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
            $default_args = [
                'id' => -1
            ];
            $args = array_merge($default_args, $args);
            $list_key = implode(',',[
                'id',
                'tenbaiviet_vi',
                'tenbaiviet_en',
                'seo_name',
                'tinhnang_loc',
                'tinhnang_loc_nhom',
                'gia_timkiem_den',
                'gia_timkiem_tu',
            ]);
            $data = [];
            $category = DB::select("SELECT ".$list_key." FROM ".$this->prefix."danhmuc WHERE showhi = 1 AND id = ? LIMIT 1", [$args['id']]);
            if(!$category)
                return $data;

            $filterFeature = !empty($category[0]->tinhnang_loc) ? explode(",", $category[0]->tinhnang_loc) : array();
            $filterFeatureGroup = !empty($category[0]->tinhnang_loc_nhom) ? json_decode($category[0]->tinhnang_loc_nhom, true) : array();
            $listOfFeature = implode(',', array_keys($filterFeatureGroup));

            $feature = DB::select("SELECT NULL as type, NULL as min, NULL as max, `tenbaiviet_vi` AS name_vi, `tenbaiviet_en` AS name_en, `id`, `id_parent`, `duongdantin` AS directory_news, `icon` ,`id_chon` AS id_picked FROM ".$this->prefix."baiviet_tinhnang WHERE showhi = 1 AND id IN (".$listOfFeature.") ORDER BY `catasort` ASC", [$args['id']]);

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
            $listOfProp->id = $category[0]->id;
            $listOfProp->name_vi = $category[0]->tenbaiviet_vi;
            $listOfProp->name_en = $category[0]->tenbaiviet_en;
            $listOfProp->name_vi = $category[0]->tenbaiviet_vi;
            $listOfProp->price_to = $category[0]->gia_timkiem_den;
            $listOfProp->price_from = $category[0]->gia_timkiem_tu;
            $listOfProp->seo_name = $category[0]->seo_name;
            $listOfProp->features = $listOfFea;
            $listOfProp->others = $listOfOther;
            $data[] = $listOfProp;
            return $data;
        }
        catch (\Throwable $th)
        {
            // return $th->getMessage();
            return false;
        }
    }
}
