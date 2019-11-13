<?php

use Riclep\Storyblok\Http\Controllers\StoryblokController;

/*
 * Clears the cache of Storyblok items
 * */
Route::post('/clear-storyblok-cache', StoryblokController::class . '@destroy')->name('clear-storyblok-cache');

/*
 * Matches any path so should be the last view specified
 */
//Route::get('/{slug?}', StoryblokController::class . '@show')->where('slug', '(.*)');