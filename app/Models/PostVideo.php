<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Comment;

class PostVideo extends Model
{
    //
    private $prefix = "";
    private $endpoint;
    
    public function __construct(){
        $this->prefix = env("DB_PREFIX");
        $this->endpoint = env("ENDPOINT_URL");
    }

    public function get_videos($args = []){
        $default_args = [
            'categories' => '',
            'user_id' => '', 
            'offer' => '',
            'order' => 'DESC',
            'order_by' => 'id',
            'random' => false,
            'post_per_page' => 6,
            'page' => 1,
        ];
        $args = array_merge($default_args, $args);
        $step = 10; //post type video
        $lang = 'vi';
        $list_key = implode(',',[
            'post.id',
            'post.id_user',
            'post.id_parent',
            'post.tenbaiviet_'.$lang.' as tenbaiviet',
            'post.mota_'. $lang.' as mota',
            'post.noidung_'.$lang.' as noidung',
            'post.ngaydang',
            'post.capnhat',
            'post.soluotxem',
            'post.soluothienthi',
            'post.step',
            'post.tinh_thanh',
            'post.quan_huyen',
            'post.phuong_xa',
            'post.icon',
            'post.dowload',
            'user.hoten',
            'user.icon as user_icon',
            'COUNT(comments.id) as total_comments'
        ]);
        //String Query
        $start = ((int)$args['page']*(int)$args['post_per_page']) - (int)$args['post_per_page'];
        $numview = (int)$args['post_per_page'];
        if($args['random']){
            $sort = "RAND()";
        } else {
            $sort = "post.". $args['order_by'] ." ". $args['order'];
        }
        $where = "";
        if( !empty($args['categories']) ){
            $where = " AND post.id_parent = ". $args['categories'] ." ";
        }
        //Query
        try {
            $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."baiviet AS post LEFT JOIN ". $this->prefix ."members AS user ON post.id_user = user.id LEFT JOIN ". $this->prefix . "binhluan AS comments ON post.id = comments.id_sp AND comments.showhi = 1 WHERE post.showhi = 1 AND post.step IN (".$step.") ". $where ." GROUP BY post.id ORDER BY $sort LIMIT $start,".$numview);
            if(!$query){
                return false;
            }
            $data = array();
            foreach($query as $key => $post){
                //$comment = new Comment();
                //$total_comment = $comment->countComment($post->id);
                $total_comment = 0;
                $data[] = [
                    'id' => $post->id,
                    'id_user' => $post->id_user,
                    'id_parent' => $post->id_parent,
                    'tenbaiviet' => $post->tenbaiviet,
                    'mota' => $post->mota,
                    'noidung' => $post->noidung,
                    'ngaydang' => $post->ngaydang,
                    'capnhat' => $post->capnhat,
                    'soluotxem' => $post->soluotxem,
                    'soluothienthi' => $post->soluothienthi,
                    'step' => $post->step,
                    'tinh_thanh' => $post->tinh_thanh,
                    'quan_huyen' => $post->quan_huyen,
                    'phuong_xa' => $post->phuong_xa,
                    'icon' => (!empty($post->icon)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->icon : "",
                    'dowload' => (!empty($post->dowload)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->dowload : "",
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
            // print_r($th->getMessage());
            return false;
        }
    }

    public function get_video($id){
        $step = 10; //post type video
        $lang = 'vi';
        $list_key = implode(',',[
            'post.id',
            'post.id_user',
            'post.id_parent',
            'post.tenbaiviet_'.$lang.' as tenbaiviet',
            'post.mota_'. $lang.' as mota',
            'post.noidung_'.$lang.' as noidung',
            'post.ngaydang',
            'post.capnhat',
            'post.soluotxem',
            'post.soluothienthi',
            'post.step',
            'post.tinh_thanh',
            'post.quan_huyen',
            'post.phuong_xa',
            'post.icon',
            'post.dowload',
            'user.hoten',
            'user.icon as user_icon',
            'COUNT(comments.id) as total_comments'
        ]);
        //String Query
        $where = " AND post.id=". $id ." ";
        //Query
        try {
            $query = DB::select("SELECT ". $list_key ." FROM ". $this->prefix ."baiviet AS post LEFT JOIN ". $this->prefix ."members AS user ON post.id_user = user.id LEFT JOIN ". $this->prefix . "binhluan AS comments ON post.id = comments.id_sp AND comments.showhi = 1 WHERE post.showhi = 1 AND post.step IN (".$step.") ". $where ." GROUP BY post.id LIMIT 0,1");
            if(!$query){
                return false;
            }
            $data = [];
            foreach($query as $key => $post){
                $data[] = [
                    'id' => $post->id,
                    'id_user' => $post->id_user,
                    'id_parent' => $post->id_parent,
                    'tenbaiviet' => $post->tenbaiviet,
                    'mota' => $post->mota,
                    'noidung' => $post->noidung,
                    'ngaydang' => $post->ngaydang,
                    'capnhat' => $post->capnhat,
                    'soluotxem' => $post->soluotxem,
                    'soluothienthi' => $post->soluothienthi,
                    'step' => $post->step,
                    'tinh_thanh' => $post->tinh_thanh,
                    'quan_huyen' => $post->quan_huyen,
                    'phuong_xa' => $post->phuong_xa,
                    'icon' => (!empty($post->icon)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->icon : "",
                    'dowload' => (!empty($post->dowload)) ? $this->endpoint.'datafiles/member/'.$post->id_user.'/'.$post->dowload : "",
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
            return false;
        }
    }

    public function get_videos_ramdom($limit, $page){
        $videos = $this->get_videos([
            'random' => true,
            'post_per_page' => $limit,
            'page' => $page
        ]);
        return $videos;
    }
}
