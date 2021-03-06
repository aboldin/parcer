<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::get('/testMatch', 'MainController@testMatch');
Route::get('/testProfit', 'MainController@testProfit');
Route::get('/testLeague', 'MainController@testLeague');
Route::get('/client', 'MainController@client');
Route::get('/token', 'MainController@token');

Route::post('/import', 'MainController@importProxy');
Route::auth();

Route::get('/home', function () {
    return redirect('/admin');
});
Route::get('/', function () {
    return redirect('/admin');
});