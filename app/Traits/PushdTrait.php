<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait PushdTrait
{

    public function register($user,$device_token)
    {
        $data = [
            "proto" => $user->proto,
            "token" => $device_token
        ];


        return $this->pushdREST("POST","subscribers",$data);
    }

    public function subscribe($pushd_id,$topic)
    {
        return $this->pushdREST("POST","subscriber/{$pushd_id}/subscriptions/{$topic}");
    }

    public function send($user,$question)
    {

        $data = [
            "title" => $question->title,
            "msg" => $question->title,
            "data.body" => $question->title,
            "data.question_id" => "{$question->id}",
            "category" => "question"
        ];

        return $this->pushdREST("POST","event/country_{$question->country_id}",$data);
    }

    private function pushdREST($request,$endpoint,$data = null)
    {
        $curl = curl_init();

        $url = env("APP_PUSHD_URL") . $endpoint;

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request,
            CURLOPT_HTTPHEADER => ["content-type: application/json"]
        ]);

        if ( $data ) {
            curl_setopt_array($curl, [
                CURLOPT_POSTFIELDS => json_encode($data)
            ]);
        }
        
        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
