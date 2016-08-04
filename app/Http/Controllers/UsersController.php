<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    /**
     * Creates a user
     * @param  Request $request The request from node passed from Firebase
     * @return [type]           [description]
     */
    public function store(Request $request)
    {

        // This is validating the Firebase structure
        $this->validate($request, ['phone' => 'required','name'=>'required']);

        try {

            $input = $request->input();

            $input['api-key'] = bin2hex(openssl_random_pseudo_bytes(32));

            $savedUser = User::create($input);

        } catch(\Illuminate\Database\QueryException $e) {

            return response()->json([
                "error"=>[
                    "type"=>"QueryException",
                    "message"=>$e->errorInfo[2],
                    "status_code" => 400
                ]
            ],400);
        }

        return response()->json([
            "success"=>[
                "status_code"=>200,
                "message" => "A new user has been created! Keep fighting the battle.",
                "data" => $savedUser
            ]
        ]); 
    }
}
