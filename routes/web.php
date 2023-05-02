<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return 'Do you like MyVisit.com?';
});

/** @see \App\Providers\RouteServiceProvider::configureRateLimiting() */
Route::middleware(['throttle:info'])->group(function () {
    Route::get('/api/getMySelf', [Controllers\UserController::class, 'getMySelf']);
    Route::post('/api/saveDepartmentIds', [Controllers\UserController::class, 'saveDepartmentIds']);
});

Route::middleware(['throttle:telegram'])->group(function () {
    Route::post('/api/notify', [Controllers\EventsController::class, 'notify']);
    Route::post('/api/repost-notify', [Controllers\EventsController::class, 'notifySubscribers']);
});
