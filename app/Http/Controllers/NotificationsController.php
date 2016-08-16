<?php

namespace App\Http\Controllers;

use App\Traits\PushdTrait;
use App\User;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
   use PushdTrait;

   public function send(Request $request)
   {

        $this->validate($request, [
            'message'=>'required',
            'image' => 'required',
            'sound' => 'required'
        ]);

        // User
        $user = User::where("id",$request->user()->id)->with("friends")->first();

        $message = "{$user->name}: {$request->input("message")}{$request->input("message")}{$request->input("message")}{$request->input("message")}{$request->input("message")}";

        $image = $request->input("image");

        $sound = $request->input("sound");

        foreach ($user->friends as $friend)
        {
            if($friend->pushd_id) {
                $this->sendNotification($user,$friend->pushd_id,$message,$image,$sound);
            }
        }
            
        return response()->json([  
            "success"=>[
                "status_code"=>200,
                "message" => "Notifications Sent!",
                "user" => $user
            ]
        ]); 
   }


   public function reply(Request $request)
   {
        $this->validate($request, [
            'replyToID' => 'required',
            'message'=>'required',
            'image' => 'required',
            'sound' => 'required'
        ]);

        $user = $request->user();
        
        $replyToID = $request->input("replyToID");
        
        $message = "{$user->name}: {$request->input("message")}";

        $image = $request->input("image");

        $sound = $request->input("sound");
        

        try {   
            $friend = User::where("id",$replyToID)->firstOrFail();

        } catch(ModelNotFoundException $e) {
            
            return response()->json([
                "error"=>[
                    "type"=>"ModelNotFoundException",
                    "message"=>"No such user exits.",
                    "status_code" => 404
                ]
            ],404);
        }

        $this->sendNotification($user,$friend->pushd_id,$message,$image,$sound);

        return response()->json([  
            "success"=>[
                "status_code"=>200,
                "message" => "Response Sent",
                "user" => $user
            ]
        ]); 

   }
}
