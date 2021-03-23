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
            'id_baiviet'     => 'required|numeric'
        ],
        [
            'id_baiviet.required' => 'id_baiviet is required!',
            'id_baiviet.numeric' => 'id_baiviet is number!'
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
        
        try
        {
            $images = (new PostImage())->getPostImage($data);
            if(is_array($images))
                return response()->json([
                    'status' => 'ok',
                    'image' => (count($images) > 1) ? $images : $images[0]
                ], 200);
            return response()->json([
                'status' => 'error',
                'code' => '',
                'message' => $images
                ], 200);
        }
        catch(\Exception $e)
        {
            return response()->json([
                'status' => 'error',
                'code' => '',
                'message' => $e->getMessage()
            ], 200);
        }
    }
}
