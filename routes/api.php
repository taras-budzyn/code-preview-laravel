<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:api')->get('user', function (Request $request) {
//    return $request->user();
//});

Route::get('member', 'Api\MemberController@index');
Route::get('client', 'Api\ClientController@index');
Route::get('member/search', 'Api\MemberController@search');
Route::post('attendance', 'Api\AttendanceController');
Route::get('clientcontract', 'Api\ClientContractController')->name('api.clientcontract');
