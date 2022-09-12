<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;

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

// Route Group User (Prefix /user as User)
Route::group(['prefix' => 'user', 'as' => 'User'], function () {
    Route::post('/photo/{id}', [UserController::class, 'storePhoto'])->name('PhotoStore');
    Route::get('/photo/{id}', [UserController::class, 'showPhoto'])->name('PhotoShow');
});
