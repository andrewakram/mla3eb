<?php

use App\Http\Controllers\Api\V1\auth\AuthController;
use App\Http\Controllers\Api\V1\user\HomeController;
use App\Http\Controllers\Api\V1\user\MySubscribersControllers;
use App\Http\Controllers\Api\V1\user\PackagesController;
use App\Http\Controllers\Api\V1\user\LocationsController;
use App\Http\Controllers\Api\V1\user\CouponsController;
use App\Http\Controllers\Api\V1\user\OrderController;
use App\Http\Controllers\Api\V1\user\NotificationsController;
use App\Http\Controllers\Api\V1\app\SettingsController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//should login first authorization
Route::group(['prefix' => "V1", 'namespace' => 'V1'], function () {
    Route::group(['prefix' => "app"], function () {

        Route::post('/book-order', [HomeController::class, 'bookOrder']);
        Route::get('/get-pitch-slots', [HomeController::class, 'getPitchSlotsByStadiumId']);

    });

});
