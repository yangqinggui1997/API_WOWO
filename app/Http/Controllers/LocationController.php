<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    private $request;
    private $prefix = "";

    public function __construct(Request $request){
        $this->request = $request;
        $this->prefix = env("DB_PREFIX");
    }

    public function getLocation()
    {
        $validator = Validator::make($this->request->all(),
        [
            'id'     => 'numeric',
            'id_parent'     => 'numeric',
        ],
        [
            'id.numeric' => 'id is number!',
            'id_parent.numeric' => 'id_parent is number!'
        ]);

        if($validator->fails())
            return response()->json([
                'status' => 'error',
                'code' => '',
                'message' => $validator->messages()
            ], 200);
        $id = $this->request->input("id");
        $lang = $this->request->input("lang");
        $lang = (is_null($lang) || empty($lang)) ? 'vi':$lang;
        $id_parent = $this->request->input("id_parent");
            
        try
        {
            $query = (new Location())->getLocation($lang,$id,$id_parent);
            if(is_array($query))
                return response()->json([
                    'status' => 'ok',
                    'location' => $query
                ], 200);
            return response()->json([
                'status' => 'error',
                'code' => '',
                'message' => $query
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
