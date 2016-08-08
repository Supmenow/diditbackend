<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
   
   public function send(Request $request)
   {
        // User
        $user = User::where("id",$request->user()->id)->with("friends")->first();

        $friends = $user->friends;

        $friendIdList = collect();

        foreach ($friends as $friend) 
        {
            if (!empty($friend->iid_token)) {
                $friendIdList->push($friend->iid_token);
            }
        }

        if($friendIdList->count() > 0) {
            
            $message = "{$user->name} just Did It.";
            
            $this->sendNotification($user->id,$friendIdList,$message);

        }

        $responseMessage = "{$friendIdList->count()} notifications sent";
        
        return response()->json([  
            "success"=>[
                "status_code"=>200,
                "message" => $responseMessage,
                "user" => $user
            ]
        ]); 
   }


   private function sendNotification($senderID,$recieverIID,$message)
    {
        $curl = curl_init();

        $post = [
            "registration_ids" => $recieverIID,
            "priority" => 10,
            "notification" => [
                "body" => $message,
                "sound" => "dong.wav",
                "click_action" => "REPLY_CATEGORY",
            ],
            "data" => [
                "userID" => $senderID,
                "image" => "smiley"
            ]
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($post),
            CURLOPT_HTTPHEADER => array(
                "authorization: key=AIzaSyBjRxMv8SHt1MgI3L-jYoXK6ST0TfapUOg",
                "content-type: application/json"
            )
        ]);
        
        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
