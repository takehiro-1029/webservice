<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {return view('welcome');});
Route::get('auth', 'Auth\Home@redirectToProvider')->name('drills.create');
Route::get('auth/twitter', 'Auth\TwitterController@twitter');
Route::get('auth/twitter/callback','Auth\TwitterController@twitterCallback');
Route::get('auth/twitter/timeline','Auth\TwitterController@getTimeline');
Route::get('googlenews','Auth\TwitterController@getNews');
Route::get('getcoincheck','Auth\TwitterController@getCoincheck');
Route::get('gettwitterComment','Auth\TwitterController@gettwitterComment');
Route::get('getCryptoComment','Auth\TwitterController@getCryptoComment');
Route::get('follow','Auth\TwitterController@follow');
Route::post('userfollow', 'Auth\TwitterController@userfollow')->name('twitter.follow');
Route::get('Callback', 'Auth\TwitterController@Callback');
Route::get('cryptocommenthome', 'Auth\TwitterController@cryptocommenthome');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
