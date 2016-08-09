<?php

namespace App\Http\Controllers;

use App\Traits\ParseNumbers;
use App\Traits\PushdTrait;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class UsersController extends Controller
{

    use ParseNumbers, PushdTrait;

    /**
     * Creates a user
     * @param  Request $request The request from node passed from Firebase
     * @return [type]           [description]
     */
    public function store(Request $request)
    {

        // This is validating the Firebase structure
        $this->validate($request, ['phone' => 'required','name'=>'required','proto'=>'required']);

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
        $this->validate($request, ['phone' => 'required',]);

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

    public function update(Request $request)
    {
        $user = $request->user();

        if ( $request->has("device_token")) {

            $request->device_token = preg_replace('/\s+/', '', $request->device_token);
            
            return $pushd = json_decode($this->register($user,$request->input("device_token")));
                
            $this->subscribe($pushd->id,"country_{$user->country_id}");

            $request->merge(["pushd_id" => $pushd->id]);

        }   

        $user = $user->update($request->all());

        return response()->json([
            "success"=>[
                "status_code"=>200,
                "message" => "The user has been udpated!",
                "user" => $user
            ]
        ]); 
    }

    /**
     * Parse contacts and register friends
     * @param  Request $request The incomming request
     */
    public function contacts(Request $request)
    {
        // Fetch the user from the Authenticated request
        $user = $request->user();

        // Grab the numbers from the request
        $numbers = $request->input("numbers");

        // Create a collection
        $phoneArray = collect([]);

        // Loop through the numbers, parse them and
        // push to $phoneArray
        foreach ($numbers as $number) {
            
            $number = $this->parseNumber($number);

            $phoneArray->push($number);

        }

        // Get all related friends
        $friends = User::whereIn("phone",$phoneArray)->where("phone","!=",$user->phone)->groupBy("phone")->get();

        // Grab the ids
        $friendIds = $friends->pluck("id");

        // Sync up the friends
        $user->friends()->sync($friendIds->toArray());

        // Update recip relationships
        foreach ($friends as $friend) 
        {
            $friend->friends()->attach($user->id);
        }

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
