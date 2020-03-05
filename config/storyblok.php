<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Storyblok Preview API key
    |--------------------------------------------------------------------------
    |
    | Enter your Storyblok Preview API key to communicate with their API.
    | The preview key allows you to access draft content and is used when
    | in the editor or when debug mode is enabled.
    |
    */
	'api_preview_key' => env('STORYBLOK_PREVIEW_API_KEY'),

	/*
    |--------------------------------------------------------------------------
    | Storyblok Public API key
    |--------------------------------------------------------------------------
    |
    | Enter your Storyblok Public API key to communicate with their API.
    | This key is used when your website is live and debug is off.
    |
    */
	'api_public_key' => env('STORYBLOK_PUBLIC_API_KEY'),

	/*
    |--------------------------------------------------------------------------
    | Storyblok draft mode
    |--------------------------------------------------------------------------
    |
    | Request draft data
    |
    */
	'draft' => env('STORYBLOK_DRAFT', false),


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
