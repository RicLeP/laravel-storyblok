<?php

use Illuminate\Support\Facades\Route;

use Riclep\Storyblok\Http\Controllers\LiveContentController;
use Riclep\Storyblok\Http\Controllers\StoryblokController;
use Riclep\Storyblok\Http\Controllers\WebhookController;

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

Route::post('/api/laravel-storyblok/clear-storyblok-cache', StoryblokController::class . '@destroy');
Route::post('/api/laravel-storyblok/live-content', LiveContentController::class . '@index');

Route::post('/api/laravel-storyblok/webhook/publish', WebhookController::class . '@publish');