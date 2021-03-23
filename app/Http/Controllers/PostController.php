<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    private $request;
    private $prefix = "";

    public function __construct(Request $request){
        $this->request = $request;
        $this->prefix = env("DB_PREFIX");
    }

    public function getRelatePosts(){
        $validator = Validator::make($this->request->all(), [
            'post_per_page' => 'required|numeric',
            'page' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        }
        $data = array();
        foreach($this->request->all() as $key => $value){
            $data[$key] = $value;
        }
        $posts_query = (new Post())->get_posts_ramdom($data);
        if(is_array($posts_query))
            return response()->json([
                'status' => 'ok',
                'posts' => $posts_query
            ], 200);
        return response()->json([
            'status' => 'error',
            'code' => 'error-happened',
            'message' => $posts_query
        ], 200);
    }

    public function getPost(){
        $validator = Validator::make($this->request->all(),[
            'id'     => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        }

        $id = $this->request->input('id');
        $posts_query = (new Post())->get_post($id);
        if(is_array($posts_query))
            return response()->json([
                'status' => 'ok',
                'post' => $posts_query[0]
            ], 200);
        return response()->json([
            'status' => 'error',
            'code' => 'error-happened',
            'message' => $posts_query
        ], 200);
    }

    public function getOrFilter()
    {
        $validator = Validator::make($this->request->all(), [
            'id_user' => 'numeric',
            'page' => 'numeric',
            'post_per_page' => 'numeric',
            'location' => 'numeric'
        ]);
        if($validator->fails())
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);

        $data = array();
        foreach($this->request->all() as $key => $value){
            $data[$key] = $value;
        }
        $posts_query = (new Post())->get_posts($data);
        if(is_array($posts_query))
            return response()->json([
                'status' => 'ok',
                'posts' => $posts_query
            ], 200);
        return response()->json([
            'status' => 'error',
            'code' => 'error-happened',
            'message' => $posts_query
        ], 200);
    }

    public function likeOrDislike($closure)
    {
        $validator = Validator::make($this->request->all(),[
            'id'     => 'required|numeric'
        ]);

        if($validator->fails())
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        if($closure($this->request))
            return response()->json([
                'status' => 'ok'
            ], 200);
        else
            return response()->json([
                'status' => 'error',
                'code' => 'error-happened',
                'message' => "false"
            ], 200);
    }

    public function like()
    {
        return $this->likeOrDislike(function ($data){return (new Post())->like($data);});
    }

    public function disLike()
    {
        return $this->likeOrDislike(function ($data){return (new Post())->disLike($data);});
    }

    public function savePost()
    {

        return $this->likeOrDislike(function ($data){return (new Post())->savePost($data);});
    }

    public function unSavePost()
    {
        return $this->likeOrDislike(function ($data) {return (new Post())->unSavePost($this->request);});
    }
}
