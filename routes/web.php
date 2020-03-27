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
Route::get('mypage','TwitterController@getNews')->name('cryptotrend.mypage');
Route::get('CryptoRank', 'TwitterController@CryptoRank')->name('cryptotrend.rank');
Route::get('twitter', 'TwitterController@twitter')->name('cryptotrend.usershow');
Route::get('twitter/callback','TwitterController@twitterCallback');
Route::get('Callback', 'TwitterController@Callback');


Route::get('twitter/timeline','TwitterController@getTimeline');
Route::get('gettwitterComment','TwitterController@gettwitterComment');
Route::get('getCryptoComment','TwitterController@getCryptoComment');


Auth::routes();

Route::get('/', function () {return view('welcome');});
Route::get('auth', 'Auth\Home@redirectToProvider')->name('drills.create');
Route::get('/home', 'HomeController@index')->name('home');
