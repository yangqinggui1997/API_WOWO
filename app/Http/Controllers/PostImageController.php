<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\PostImage;
use Illuminate\Http\Request;

class PostImageController extends Controller
{
    private $request;
    private $prefix = "";

    public function __construct(Request $request){
        $this->request = $request;
        $this->prefix = env("DB_PREFIX");
    }

    public function getPostImage()
    {
        $validator = Validator::make($this->request->all(),
        [
            'id_post'     => 'required|numeric'
        ],
        [
            'id_post.required' => 'id_post is required!',
            'id_post.numeric' => 'id_post is number!'
        ]);

        if($validator->fails())
            return response()->json([
                'status' => 'error',
                'code' => '',
                'message' => $validator->messages()
            ], 200);
        $data = array();
        foreach($this->request->all() as $key => $value){
            $data[$key] = $value;
        }
        $images = (new PostImage())->getPostImage($data);
        if(is_array($images))
            return response()->json([
                'status' => 'ok',
                'image' => array(['count' => count($images), 'image' => $images])
            ], 200);
        return response()->json([
            'status' => 'error',
            'code' => 'error-happened',
            'message' => $images
            ], 200);
    }
}
