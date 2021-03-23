<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\PostVideo;
use App\Models\Post;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class PostVideoController extends Controller
{
    //
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public function getVideos(){
        $data = array();
        foreach($this->request->all() as $key => $value){
            $data[$key] = $value;
        }
        $posts_query = (new PostVideo())->get_videos($data);
        if($posts_query){
            return response()->json([
                'status' => 'ok',
                'posts' => $posts_query
            ], 200);
        } else {
            return response()->json([
                'status' => 'ok',
                'posts' => []
            ], 200);
        }
    }

    public function getVideo(){
        $validator = Validator::make($this->request->all(), [
            'id' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'code' => '',
                'message' => $validator->messages()
            ], 200);
        }
        
        $id = $this->request->input('id');
        $posts_query = (new PostVideo())->get_video($id);
        if($posts_query){
            return response()->json([
                'status' => 'ok',
                'post' => $posts_query[0]
            ], 200);
        } else {
            return response()->json([
                'status' => 'ok',
                'post' => []
            ], 200);
        }
    }

    public function getRelateVideos(){
        $validator = Validator::make($this->request->all(), [
            'post_per_page' => 'required|numeric',
            'page' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'code' => '',
                'message' => $validator->messages()
            ], 200);
        }

        $page = $this->request->input('page');
        $limit = $this->request->input('post_per_page');
        $posts_query = (new PostVideo())->get_videos_ramdom($limit, $page);
        if($posts_query){
            return response()->json([
                'status' => 'ok',
                'posts' => $posts_query
            ], 200);
        } else {
            return response()->json([
                'status' => 'ok',
                'posts' => []
            ], 200);
        }
    }
}
