<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CommentController extends Controller
{
    private $request;
    private $prefix = "";

    public function __construct(Request $request){
        $this->request = $request;
        $this->prefix = env("DB_PREFIX");
    }

    public function getComment()
    {
        $validator = Validator::make($this->request->all(),
        [
            'id'     => 'numeric',
            'id_post'     => 'numeric',
            'id_parent'     => 'numeric',
            'page'     => 'numeric',
            'comments_per_page'     => 'numeric',
        ],
        [
            'id.numeric' => 'id is number!',
            'id_post.numeric' => 'id_post is number!',
            'id_parent.numeric' => 'id_parent is number!',
            'page.numeric' => 'page is number!',
            'comments_per_page.numeric' => 'comments_per_page is number!'
        ]);

        if($validator->fails())
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        $data = [];
        foreach($this->request->all() as $key => $value){
            $data[$key] = $value;
        }
        $query = (new Comment())->get($data);
        if(is_array($query))
            return response()->json([
                'status' => 'ok',
                'comment' => array(['count' => (new Comment)->countComment($data), 'comment' => $query])
            ], 200);
        return response()->json([
            'status' => 'error',
            'code' => 'error-happened',
            'message' => $query
        ], 200);
    }

    public function createComment()
    {
        if(!$this->request->auth)
            return response()->json([
                'status' => 'error',
                'code' => 'you-need-login',
                'message' => __('you need login!')
            ], 200);
        $validator = Validator::make($this->request->all(),
        [
            'id_post'     => 'numeric',
            'id_parent'     => 'numeric'
        ],
        [
            'id_post.numeric' => 'id_post is number!',
            'id_parent.numeric' => 'id_parent is number!'
        ]);

        if($validator->fails())
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        $id_post = \App\AppHelper::LOC_charnew_v2($this->request->id_post);
        $id_parent = $this->request->id_parent;
        $content = \App\AppHelper::LOC_charnew_v2($this->request->content);
        $hoten = \App\AppHelper::LOC_charnew_v2($this->request->auth->hoten);
        $query = false;
        $showhi = DB::table("seo")->select("is_duyet_bl")->first();
        $ip = \App\AppHelper::GET_ip();
        $query = (new Comment())->create(
            [
            "id_sp" => $id_post,
            "ip_gui" => "'".$ip."'",
            "uid" => $this->request->auth->id,
            "tenbaiviet_vi" => "'".$hoten."'",
            "noidung_vi" => "'".$content."'",
            "loai_binhluan" => 0,
            "showhi" => $showhi->is_duyet_bl,
            "ngay_dang" => time(),
            "id_parent" => $id_parent
        ]);
        if($query)
            return response()->json([
                'status' => 'ok'
            ], 200);
        return response()->json([
            'status' => 'error',
            'code' => 'error-happened',
            'message' => $query
        ], 200);
    }
}
