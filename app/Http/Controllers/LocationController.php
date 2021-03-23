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
                'code' => 'validate-fail',
                'message' => $validator->messages()
            ], 200);
        $data = array();
        foreach($this->request->all() as $key => $value){
            $data[$key] = $value;
        }
        $query = (new Location())->getLocation($data);
        if(is_array($query))
            return response()->json([
                'status' => 'ok',
                'location' => $query
            ], 200);
        return response()->json([
            'status' => 'error',
            'code' => 'error-happened',
            'message' => $query
        ], 200);
    }
}
