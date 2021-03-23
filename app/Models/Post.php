<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\Location;

class Post extends Model
{
    private $prefix = "";
    private $endpoint;

    public function __construct(){
        $this->prefix = env("DB_PREFIX");
        $this->endpoint = env("ENDPOINT_URL");
    }

    public function get_posts($args = []){
        $default_args = [
            'id_user' => '',
            'offer' => '',
            'order' => 'DESC',
            'order_by' => 'id',
            'random' => false,
            'post_per_page' => 6,
            'page' => 1,
            'location' => '',
            'categories' => '',
            'key_seacrh' => '',
            'price' => '',
            'feature' => '',
            'other_filter' => ''
        ];

        $args = array_merge($default_args, $args);
        $step = 2; //post type post
        $lang = 'vi';
        $list_key = implode(',',[
            'post.id',
            'post.id_user',
            'post.id_parent',
            'post.tenbaiviet_'.$lang.' as tenbaiviet',
            'post.mota_'. $lang.' as mota',
            'post.noidung_'.$lang.' as noidung',
            'post.seo_name',
            'post.is_noibat',
            'post.is_uutien',
            'post.is_daytin',
            'post.giatien',
            'post.ngaydang',
            'post.capnhat',
            'post.soluotxem',
            'post.soluothienthi',
            'post.step',
            'post.tinh_thanh',
            'post.quan_huyen',
            'post.phuong_xa',
            'post.dia_chi',
            'post.so_dien_thoai',
            'post.icon',
            'post.dowload',
            'post.bv_like',
            'user.hoten',
            'user.icon as user_icon',
            'COUNT(comments.id) as total_comments'
        ]);
        //String Query
        $start = ((int)$args['page']*(int)$args['post_per_page']) - (int)$args['post_per_page'];
        $numview = (int)$args['post_per_page'];

        $where = " AND show_thanhtoan = 0 AND kh_antin = 0 AND is_uutien < '".time()."' AND is_daytin < '".time()."' ";
        $sort = "";
        if($args['random']) //get post by random
            $sort = "RAND()";
        else
        {
            $order_by = explode(',', $args['order_by']);
            $order = explode(',', $args['order']);
            foreach($order_by as $key => $value)
                $sort .= $sort ? ", post.". $value ." ". $order[$key] : " post.". $value ." ". $order[$key];
        }
        if(!empty($args['user_id']))
        {
            $where .= " AND post.user_id = ". $args['user_id'] ." ";
        }
        if( !empty($args['categories']) ) // filter option follow categories
            $where .= " AND post.id_parent = ". $args['categories'] ." ";

        if( !empty($args['location']) ) // filter option follow location
            $where .= " AND (post.tinh_thanh = ". $args['location'] ." OR post.quan_huyen = ". $args['location'] ." OR post.phuong_xa = ". $args['location'] .") ";
        if( !empty($args['price']) ) // filter option follow price
        {
            $price = json_decode($args['price']);
            $where .= property_exists($price, 'min') && property_exists($price, 'max') ? " AND (post.giatien BETWEEN ". $price->min." AND ".$price->max.") " : (property_exists($price, 'min') ? " AND post.giatien >= ". $price->min : (property_exists($price, 'max') ? " AND post.giatien <= ". $price->max :""));
        }
        if(!empty($args['feature'])) // filter options follow features of post
        {
            $feature = json_decode($args['feature']);
            $condition = "";
            foreach($feature as $key => $value)
                $condition .= $condition ? " OR "." ( `id_thuoctinh` = '".$key."' AND ".(property_exists($value, 'min') && property_exists($value, 'max') ? " thuoctinh_val BETWEEN ". $value->min." AND ".$value->max.")" : (property_exists($value, 'min') ? " thuoctinh_val >= ". $value->min.") " : (property_exists($value, 'max') ? " thuoctinh_val <= ". $value->max.")" : "1=1"))) : " ( `id_thuoctinh` = '".$key."' AND ".(property_exists($value, 'min') && property_exists($value, 'max') ? " thuoctinh_val BETWEEN ". $value->min." AND ".$value->max.")" : (property_exists($value, 'min') ? " thuoctinh_val >= ". $value->min.") " : (property_exists($value, 'max') ? " thuoctinh_val <= ". $value->max.")" : "1=1")));
            $where .= " AND post.id IN (SELECT `id_parent` FROM ". $this->prefix . "baiviet_thuoctinh WHERE ".$condition.")";
        }
        if(!empty($args['other_filter']))
        {
            $condition = "";
            $other_filter = json_decode($args['other_filter']);
            if(property_exists($other_filter, 'buy_or_sale')) // filter option follow categories of bussiness
            {
                $buy_or_sale = explode(',', $other_filter->buy_or_sale);
                foreach($buy_or_sale as $bos)
                    $condition .= $condition ? " OR "." (`id_thuoctinh` = 21 AND `thuoctinh_val` = '".$bos."')" : " (`id_thuoctinh` = 21 AND `thuoctinh_val` = '".$bos."')";
            }
            if(property_exists($other_filter, 'post_by')) // filter option follow categories of users
            {
                $post_by = explode(',', $other_filter->post_by);
                foreach($post_by as $bos)
                    $condition .= $condition ? " OR "." (`id_thuoctinh` = 20 AND `thuoctinh_val` = '".$bos."')" : " (`id_thuoctinh` = 20 AND `thuoctinh_val` = '".$bos."')";
            }
            if(property_exists($other_filter, 'sort_by')) // filter option follow sort for post
                switch ($other_filter->sort_by)
                {
                    case 23: // sort news by new before
                        $sort = "`opt1` DESC, `catasort` DESC, `id` DESC";
                        break;
                    case 24: // sort news by lowest price before
                        $sort = "`giatien` ASC, `opt1` DESC, `catasort` DESC, `id` DESC";
                        break;
                    case 25: // sort news by highest price before
                        $sort = "`giatien` DESC, `opt1` DESC, `catasort` DESC, `id` DESC";
                        break;
                    default:
                        break;
                }
            $where .= " AND post.id IN (SELECT `id_parent` FROM ". $this->prefix . "baiviet_loaitin WHERE ".$condition.")";
        }

        $where .= " AND post.id_user IN (SELECT `id` FROM ". $this->prefix . "members WHERE `showhi` = 1 AND `phanquyen` = 0)";
        //Query
        try {
            $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."baiviet AS post LEFT JOIN ". $this->prefix ."members AS user ON post.id_user = user.id LEFT JOIN ". $this->prefix . "binhluan AS comments ON post.id = comments.id_sp AND comments.showhi = 1 WHERE post.showhi = 1 AND post.step IN (".$step.") ". $where ." GROUP BY post.id ".($sort ? " ORDER BY ".$sort : "")." LIMIT $start,".$numview);
            if(!$query)
                return false;
            $data = [];
            foreach($query as $key => $post){
                $data[] = [
                    'id' => $post->id,
                    'id_user' => $post->id_user,
                    'id_parent' => $post->id_parent,
                    'tenbaiviet' => $post->tenbaiviet,
                    'mota' => $post->mota,
                    'noidung' => $post->noidung,
                    'seo_name' => $post->seo_name,
                    'giatien' => $post->giatien,
                    'is_noibat' => $post->is_noibat,
                    'is_uutien' => $post->is_uutien,
                    'is_daytin' => $post->is_daytin,
                    'ngaydang' => $post->ngaydang,
                    'capnhat' => $post->capnhat,
                    'soluotxem' => $post->soluotxem,
                    'soluothienthi' => $post->soluothienthi,
                    'step' => $post->step,
                    'tinh_thanh' => $post->tinh_thanh,
                    'quan_huyen' => $post->quan_huyen,
                    'phuong_xa' => $post->phuong_xa,
                    'dia_chi' => $post->dia_chi,
                    'so_dien_thoai' => $post->so_dien_thoai,
                    'icon' => (!empty($post->icon)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->icon : "",
                    'dowload' => (!empty($post->dowload)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->dowload : "",
                    'bv_like' => $post->bv_like,
                    'comments' => $post->total_comments,
                    'user' => [
                        'id' => $post->id_user,
                        'hoten' => $post->hoten,
                        'icon' => (!empty($post->user_icon)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->user_icon : ""
                    ],
                ];
            }
            return $data;
        } catch (\Throwable $th) {
            return $th->getMessage();
            // return false;
        }
    }

    public function get_post($id){
        $step = 2; //post type video
        $lang = 'vi';
        $list_key = implode(',',[
            'post.id',
            'post.id_user',
            'post.id_parent',
            'post.tenbaiviet_'.$lang.' as tenbaiviet',
            'post.mota_'. $lang.' as mota',
            'post.noidung_'.$lang.' as noidung',
            'post.seo_name',
            'post.is_noibat',
            'post.is_uutien',
            'post.is_daytin',
            'post.giatien',
            'post.ngaydang',
            'post.capnhat',
            'post.soluotxem',
            'post.soluothienthi',
            'post.step',
            'post.tinh_thanh',
            'post.quan_huyen',
            'post.phuong_xa',
            'post.dia_chi',
            'post.so_dien_thoai',
            'post.icon',
            'post.dowload',
            'post.bv_like',
            'user.hoten',
            'user.icon as user_icon',
            'COUNT(comments.id) as total_comments'
        ]);
        //String Query
        $where = " AND post.id=". $id ." ";
        //Query
        try {
            $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."baiviet AS post LEFT JOIN ". $this->prefix ."members AS user ON post.id_user = user.id LEFT JOIN ". $this->prefix . "binhluan AS comments ON post.id = comments.id_sp AND comments.showhi = 1 WHERE post.showhi = 1 AND post.step IN (".$step.") ". $where ." GROUP BY post.id LIMIT 0,1");
            $data = [];
            foreach($query as $key => $post){
                $data[] = [
                    'id' => $post->id,
                    'id_user' => $post->id_user,
                    'id_parent' => $post->id_parent,
                    'tenbaiviet' => $post->tenbaiviet,
                    'mota' => $post->mota,
                    'noidung' => $post->noidung,
                    'seo_name' => $post->seo_name,
                    'is_noibat' => $post->is_noibat,
                    'is_uutien' => $post->is_uutien,
                    'is_daytin' => $post->is_daytin,
                    'ngaydang' => $post->ngaydang,
                    'capnhat' => $post->capnhat,
                    'soluotxem' => $post->soluotxem,
                    'soluothienthi' => $post->soluothienthi,
                    'step' => $post->step,
                    'tinh_thanh' => $post->tinh_thanh,
                    'quan_huyen' => $post->quan_huyen,
                    'phuong_xa' => $post->phuong_xa,
                    'dia_chi' => $post->dia_chi,
                    'so_dien_thoai' => $post->so_dien_thoai,
                    'icon' => (!empty($post->icon)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->icon : "",
                    'dowload' => (!empty($post->dowload)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->dowload : "",
                    'bv_like' => $post->bv_like,
                    'comments' => $post->total_comments,
                    'user' => [
                        'id' => $post->id_user,
                        'hoten' => $post->hoten,
                        'icon' => (!empty($post->user_icon)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->user_icon : ""
                    ],
                ];
            }
            return $data;
        } catch (\Throwable $th) {
            //print_r($th->getMessage());
            // return $th->getMessage();
            return false;
        }
    }

    // dislike bai viet
    public function disLike($request)
    {
        try
        {
            $bv = DB::select("SELECT bv_like FROM ".$this->prefix."baiviet WHERE id = ?", [$request->id]);
            if((int)$bv[0]->bv_like >= 1)
                $countLike = $bv[0]->bv_like - 1;
            else
                $countLike = 0;
            DB::transaction(function () use($request, $countLike){
                DB::table("baiviet")->where('id', $request->id)->update(['bv_like' => $countLike]);
            }, 5);
            return true;
        }
        catch(\Throwable $thr)
        {
            //print_r($thr->getMessage());
            return false;
        }
    }

    // like bai viet
    public function like($request)
    {
        try
        {
            $bv = DB::select("SELECT bv_like FROM ".$this->prefix."baiviet WHERE id = ?", [$request->id]);
            $countLike = (int)$bv[0]->bv_like + 1;
            DB::transaction(function () use($request, $countLike){
                DB::table("baiviet")->where('id', $request->id)->update(['bv_like' => $countLike]);
            }, 5);
            return true;
        }
        catch(\Throwable $thr)
        {
            //print_r($thr->getMessage());
            return false;
        }
    }

    public function saveOrUnSavePost($request, $save = 1)
    {
        try
        {
            $bv = DB::select("SELECT showhi FROM ".$this->prefix."yeuthich  WHERE id_baiviet = ? AND id_member = ? LIMIT 1", [$request->id, $request->auth->id]);
            if(count($bv))
                DB::transaction(function () use($request, $save)
                {
                    DB::table("yeuthich")->where([["id_baiviet", $request->id_baiviet], ["id_member", $request->auth->id]])->update(["showhi" => $save]);
                },5);
            else if($save)
                DB::transaction(function () use($request, $save)
                {
                    DB::table("yeuthich")->insert(["id_baiviet" => $request->id, "id_member" => $request->auth->id, "showhi" => $save, "the_loai" => 1]);
                },5);
            return true;
        }
        catch(\Throwable $thr)
        {
            //print_r($thr->getMessage());
            return false;
        }
    }

    public function savePost($request)
    {
        return (new Post)->saveOrUnSavePost($request);
    }

    public function unSavePost($request)
    {
        return (new Post)->saveOrUnSavePost($request, 0);
    }

    public function get_posts_ramdom($limit, $page){
        $videos = $this->get_posts([
            'random' => true,
            'post_per_page' => $limit,
            'page' => $page
        ]);
        return $videos;
    }
}
