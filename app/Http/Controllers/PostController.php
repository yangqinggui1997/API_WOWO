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
                'code' => '',
                'message' => $validator->messages()
            ], 200);
        }

        $page = $this->request->input('page');
        $limit = $this->request->input('post_per_page');
        $posts_query = (new Post())->get_posts_ramdom($limit, $page);
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
        if($posts_query){
            return response()->json([
                'status' => 'ok',
                'post' => $posts_query
            ], 200);
        } else {
            return response()->json([
                'status' => 'ok',
                'post' =>  []
            ], 200);
        }
    }

    public function getOrFilter($closure)
    {
        $validator = Validator::make($this->request->all(), [
            'id_user' => 'numeric',
            'page' => 'numeric',
            'post_per_page' => 'numeric',
            'location' => 'numeric'
        ]);
        $data = array();
        foreach($this->request->all() as $key => $value){
            $data[$key] = $value;
        }
        $posts_query = $closure($data);
        if($posts_query){
            return response()->json([
                'status' => 'ok',
                'posts' => $posts_query
            ], 200);
        } else {
            return response()->json([
                'status' => 'ok',
                'posts' => $posts_query
            ], 200);
        }
    }

    public function getPosts()
    {
        return $this->getOrFilter(function($data){return (new Post())->get_posts($data);});
    }

    public function filter()
    {
        return $this->getOrFilter(function($data){return (new Post())->get_posts($data);});
    }

    public function likeOrDislike($closure)
    {
        if($closure)
            return response()->json([
                'status' => 'ok'
            ], 200);
        else
            return response()->json([
                'status' => 'error',
                'code' => 'have-wrong',
                'message' => "Something went wrong!"
            ], 200);
    }

    public function like()
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

        return $this->likeOrDislike((new Post())->like($this->request));
    }

    public function disLike()
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

        return $this->likeOrDislike((new Post())->disLike($this->request));
    }

    public function saveOrUnSavePost($closure)
    {
        if($closure)
            return response()->json([
                'status' => 'ok',
            ], 200);
        return response()->json([
                'status' => 'error',
                'code' => 'have-wrong',
                'message' => "Something went wrong!"
            ], 200);

    }

    public function savePost()
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

        return $this->saveOrUnSavePost((new Post())->savePost($this->request));
    }

    public function unSavePost()
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

        return $this->saveOrUnSavePost((new Post())->unSavePost($this->request));
    }
}
