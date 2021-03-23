<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Validator;
use Carbon\Carbon;

class CategoryController extends Controller
{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public function get($closure)
    {
        $validator = Validator::make($this->request->all(),[
            'id' => 'numeric',
            'id_parent' => 'numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        }
        $data = [];
        foreach($this->request->all() as $key => $value){
            $data[$key] = $value;
        }
        $categories = $closure($data);
        if($categories)
            return response()->json([
                'status' => 'ok',
                'categories' => $categories
            ], 200);
        else{
            return response()->json([
                'status' => 'error',
                'categories' => []
            ], 200);
        }
    }

    public function getCategory()
    {
        return $this->get(function($data){return (new Category())->getCategory($data);});
    }

    public function getPropertiesForFilter()
    {
        return $this->get(function($data){return (new Category())->getPropertiesForFilter($data);});
    }
}
