<?php

namespace App\Http\Controllers;


use App\Models\Celebrity;
use App\Models\HostCelebrity;
use Illuminate\Http\Request;



class CelebrityController extends Controller
{


    /*
    Host API
    */

    public function remoteSync(Request $request)
    {


        if(
            $request->header('X-API-KEY')
            != config('remote-models.api-key')
        )
        {

            return response()->json([
                'message'=>'Unauthorized'
            ],401);

        }



        return response()->json([

            'data'=>HostCelebrity::all()

        ]);


    }


    /*
    Local Remote Model Test
    */


    public function index()
    {

        $data =
        Celebrity::remote()
        ->get();

        return response()->json($data);

    }

}