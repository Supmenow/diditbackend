<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {

	return response()->json([
            "success"=>[
                "status_code"=>200,
                "message" => "Welcome to did it. Boast about your conquests."
            ]
        ]); 
});


$app->group(['prefix' => 'api/v1','middleware'=>'auth','namespace'=>"App\Http\Controllers"], function () use ($app) {
	
	// Create a user
	$app->post('users', "UsersController@store");

});