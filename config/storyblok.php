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
    | Specify which Storyblok API region to use
    |--------------------------------------------------------------------------
    |
    | Defaults to null which should be the original EU region
    |
    */
    'api_region' => null,

    /*
    |--------------------------------------------------------------------------
    | Use SSL when calling the Storyblok API
    |--------------------------------------------------------------------------
    |
    | Request content from the secure https address or just http
    |
    */
    'use_ssl' => true,

    /*
    |--------------------------------------------------------------------------
    | Specify which Storyblok API region to use
    |--------------------------------------------------------------------------
    |
    | Defaults to null which should be the original EU region
    |
    */
	'api_region' => null,

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
    | Storyblok Personal access token
    |--------------------------------------------------------------------------
    |
    | Enter your Storyblok Personal access token to access their management API
    |
    */
    'oauth_token' => env('STORYBLOK_OAUTH_TOKEN', null),

    /*
    |--------------------------------------------------------------------------
    | Storyblok Space ID
    |--------------------------------------------------------------------------
    |
    | Enter your Storyblok space ID for use with the management API
    |
    */
    'space_id' => env('STORYBLOK_SPACE_ID', null),

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
    'cache' => env('STORYBLOK_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Cache duration
    |--------------------------------------------------------------------------
    |
    | Specifies how many minutes to cache responses from Storkyblok for.
    |
    */
    'cache_duration' => env('STORYBLOK_DURATION','60'),

    /*
    |--------------------------------------------------------------------------
    | Component class namespaces
    |--------------------------------------------------------------------------
    |
    | A list of name spaces to search when finding Blocks and Fields. They are
    | listed in the order searched and loaded.
    |
    */
    'component_class_namespace' => ['App\Storyblok\\'],

    /*
    |--------------------------------------------------------------------------
    | View folder path
    |--------------------------------------------------------------------------
    |
    | Sets the folder where views will be stored under /resources/views
    |
    */
    'view_path' => 'storyblok.',

    /*
    |--------------------------------------------------------------------------
    | Webhook secret
    |--------------------------------------------------------------------------
    |
    | Webhook from space settings
    | https://www.storyblok.com/docs/guide/in-depth/webhooks
    |
    */
    'webhook_secret' => env('STORYBLOK_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Asset domain
    |--------------------------------------------------------------------------
    |
    | Storyblok asset URL, can be customized if proxy is setup
    | https://www.storyblok.com/docs/custom-assets-domain
    |
    */
    'asset_domain' => env('STORYBLOK_ASSET_DOMAIN', 'a.storyblok.com'),

    /*
    |--------------------------------------------------------------------------
    | Image service domain
    |--------------------------------------------------------------------------
    |
    | Can be customized to proxy image service requests over a custom domain
    |
    */
    'image_service_domain' => env('STORYBLOK_IMAGE_SERVICE_DOMAIN', 'a.storyblok.com'),

    /*
    |--------------------------------------------------------------------------
    | Image transformation driver
    |--------------------------------------------------------------------------
    |
    | The class used for transforming images Fields / image URLs
    |
    */
    'image_transformer' => \Riclep\Storyblok\Support\ImageTransformers\Storyblok::class,

    /*
    |--------------------------------------------------------------------------
    | Resolve story links in content
    |--------------------------------------------------------------------------
    |
    | Resolve links to stories when using link and multi link fields, valid
    | settings are 'url', 'story' or false
    |
    */
    'resolve_links' => false,

    /*
    |--------------------------------------------------------------------------
    | Enable editor live preview
    |--------------------------------------------------------------------------
    |
    | This turns on live preview of changes in the editor if correctly set up
    |
    */
    'live_preview' => true,

    /*
    |--------------------------------------------------------------------------
    | Editor live preview selector
    |--------------------------------------------------------------------------
    |
    | Class or ID selector for the HTML element wrapping your live preview content
    |
    */
    'live_element' => '.storyblok-live',

    /*
    |--------------------------------------------------------------------------
    | Name of the field to be used for settings
    |--------------------------------------------------------------------------
    |
    | Set the field name to be used to store the generic settings components
    |
    */
    'settings_field' => 'settings',
];
