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
Route::get('/', 'TwitterController@top')->name('top')->middleware('checkedtop');
Route::get('news','TwitterController@getNews')->name('cryptotrend.news')->middleware('check');
Route::get('CryptoRank', 'TwitterController@CryptoRank')->name('cryptotrend.rank')->middleware('check');
Route::get('twitter', 'TwitterController@twitter')->name('cryptotrend.usershow')->middleware('check');
Route::get('twitter/callback','TwitterController@twitterCallback')->middleware('check');
Route::get('show', 'TwitterController@FollowedShow')->middleware('check');
Auth::routes();
Route::get('test', 'TwitterController@test1');