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
        // User
        $user = User::where("id",$request->user()->id)->with("friends")->first();

        $message = "{$user->name} just Did It.";
            
        $this->sendNotification($user,$message);

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
        $this->validate($request, ['replyTo' => 'required','message'=>'required']);

        $replyID = $request->input("replyToID");
        
        $message = $request->input("message");

        $user = $request->user();

        try {   
            $friend = User::where("id",$replyID)->firstOrFail();

        } catch(ModelNotFoundException $e) {
            
            return response()->json([
                "error"=>[
                    "type"=>"ModelNotFoundException",
                    "message"=>"No such user exits.",
                    "status_code" => 404
                ]
            ],404);
        }

        $this->sendReply($user,$friend,$message);

        return response()->json([  
            "success"=>[
                "status_code"=>200,
                "message" => "Response Sent",
                "user" => $user
            ]
        ]); 

   }
}
