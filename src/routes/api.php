<?php

use Illuminate\Support\Facades\Route;

use Riclep\Storyblok\Http\Controllers\LiveContentController;

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

Route::post('/live-content', LiveContentController::class . '@index')->as('storyblok-live-content');