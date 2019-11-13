<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Storyblok API key
    |--------------------------------------------------------------------------
    |
    | Enter your Storyblok API key to communicate with their API.
    |
    */
	'api_key' => env('STORYBLOK_API_KEY'),

	/*
    |--------------------------------------------------------------------------
    | Storyblok debug
    |--------------------------------------------------------------------------
    |
    | Enable debug mode for Storyblok. This prints useful data to the screen.
    |
    */
	'debug' => env('STORYBLOK_DEBUG'),

	/*
    |--------------------------------------------------------------------------
    | Enable caching
    |--------------------------------------------------------------------------
    |
    | Enable caching the Storyblok API response.
    |
    */
	'cache' => true,

	/*
    |--------------------------------------------------------------------------
    | Cache duration
    |--------------------------------------------------------------------------
    |
    | Specifies how many minutes to cache responses from Storkyblok for.
    |
    */
	'cache_duration' => '60',

	/*
    |--------------------------------------------------------------------------
    | Cache duration
    |--------------------------------------------------------------------------
    |
    | Sets the namespace for the Page and Block classes
    |
    */
	'component_class_namespace' => 'App\Storyblok\\',

	/*
    |--------------------------------------------------------------------------
    | Cache duration
    |--------------------------------------------------------------------------
    |
    | Sets the folder where views will be stored under /resources/views
    |
    */
	'view_path' => 'storyblok.',



];
