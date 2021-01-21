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

Route::post('/user/check', 'UserController@check_user');
Route::get('/user/get-data', 'UserController@get_table_data');
Route::get('/user/get-sites', 'UserController@get_sites_data');
Route::get('/user/get-zones','UserController@getZones');
Route::post('/user/search-site', 'UserController@getSiteByAddress');
Route::get('/user/get-sr/{id}', 'UserController@getSR');
Route::get('/user/get-site/{id}', 'UserController@getSiteByID');
Route::post('/user/upload-data','UserController@UploadData');
Route::get('/user/download-data', 'UserController@downloadData');
