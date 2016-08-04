<?php

namespace App\Http\Controllers;

use App\Traits\ParseNumbers;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    use ParseNumbers;

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

            $input['phone'] = $this->parseNumber($input["phone"]);

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

        Log::info("User Created", ["user"=>$savedUser->name,"phone"=>$savedUser->phone]);

        return response()->json([  
            "success"=>[
                "status_code"=>200,
                "message" => "A new user has been created! Keep fighting the battle.",
                "user" => $savedUser
            ]
        ]); 
    }

    public function check(Request $request)
    {
        $this->validate($request, ['phone' => 'required']);

        $number = $this->parseNumber($request->input("phone"));

        try {   
            $user = User::where("phone",$number)->firstOrFail();

        } catch(ModelNotFoundException $e) {
            
            return response()->json([
                "error"=>[
                    "type"=>"ModelNotFoundException",
                    "message"=>"No such user exits.",
                    "status_code" => 404
                ]
            ],404);
        }

        Log::info("User is logged in", ["user"=>$user->name,"phone"=>$user->phone]);

        return response()->json([
            "success"=>[
                "status_code"=>200,
                "message" => "A user you have asked for, a user you shall receive.",
                "user" => $user
            ]
        ]); 

    }

    public function show(Request $request)
    {
        try {   
            $user = User::where("id",$request->user()->id)->firstOrFail();

        } catch(ModelNotFoundException $e) {
        
            return response()->json([
                "error"=>[
                    "type"=>"ModelNotFoundException",
                    "message"=>"No such user exits.",
                    "status_code" => 404
                ]
            ],404);
        }

        Log::info("Showing User", ["user"=>$user->name,"phone"=>$user->phone]);

        return response()->json([
            "success"=>[
                "status_code"=>200,
                "message" => "A user you have asked for, a user you shall receive.",
                "user" => $user
            ]
        ]); 
    }

    public function contacts(Request $request)
    {
        $user = $request->user();

        $numbers = $request->input("numbers");

        $phoneArray = collect([]);

        foreach ($numbers as $number) {
            
            $number = $this->parseNumber($number);

            $phoneArray->push($number);
        }

        $friend = User::whereIn("phone",$phoneArray)->get();

        $friendIds = $friend->pluck("id");
        
        $user->friends()->sync($friendIds->toArray());

        $user = User::where("id",$user->id)->with("friends")->first();

        Log::info("Getting friends for User", ["user"=>$user->name,"phone"=>$user->phone,"contact_count"=>$user->friends->count()]);

        return response()->json([
            "success"=>[
                "status_code"=>200,
                "message" => "Here are your friends!.",
                "user" => $user
            ]
        ]); 
    }
}
