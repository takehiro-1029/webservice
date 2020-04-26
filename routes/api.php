<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api'], function() {
  Route::post('/follow', 'UseAxiosController@follow');
  Route::post('/autofollow', 'UseAxiosController@autofollow');
  Route::get('/coincheck', 'UseAxiosController@coincheck');
  Route::get('/hourcomment', 'UseAxiosController@hourcomment');
  Route::get('/daycomment', 'UseAxiosController@daycomment');
  Route::get('/weekcomment', 'UseAxiosController@weekcomment');
  Route::get('/usershow', 'UseAxiosController@twitterusershow');
});
