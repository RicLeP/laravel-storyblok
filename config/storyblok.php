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
    'api_preview_key' => env('STORYBLOK_PREVIEW_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Storyblok Public API key
    |--------------------------------------------------------------------------
    |
    | Enter your Storyblok Public API key to communicate with their API.
    | This key is used when your website is live and debug is off.
    |
    */
    'api_public_key' => env('STORYBLOK_PUBLIC_API_KEY', ''),

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
    | Specify which Content Delivery API region-specific base URL to use
    |--------------------------------------------------------------------------
    |
    | Defaults to api.storyblok.com which should be the original EU region
    |
    */
    'delivery_api_base_url' => 'api.storyblok.com',

    /*
    |--------------------------------------------------------------------------
    | Specify which Management API region-specific base URL to use
    |--------------------------------------------------------------------------
    |
    | Defaults to mapi.storyblok.com which should be the original EU region
    |
    */
    'management_api_base_url' => 'mapi.storyblok.com',

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
    'cache_duration' => env('STORYBLOK_DURATION',60),

    /*
    |--------------------------------------------------------------------------
    | Enable Storyblok client caching
    |--------------------------------------------------------------------------
    |
    | Set the cache driver for the Storyblok client.
    |
    */
    'sb_cache_driver' => env('STORYBLOK_SB_CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Set Storyblok client cache path
    |--------------------------------------------------------------------------
    |
    | Set the cache path for the Storyblok client (optional)
    |
    */
    'sb_cache_path' => env('STORYBLOK_SB_CACHE_PATH', storage_path('framework/cache/data')),

    /*
    |--------------------------------------------------------------------------
    | Set Storyblok client cache duration
    |--------------------------------------------------------------------------
    |
    | Set the cache duration for the Storyblok client
    |
    */
    'sb_cache_lifetime' => env('STORYBLOK_SB_CACHE_LIFETIME', 3600),

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
    | Raster image extensions
    |--------------------------------------------------------------------------
    |
    | Used to determine if the image field content is a raster image, do not
    | include SVGs or other vector formats here. This is used to determine
    | if the image should be transformed or not.
    |
    */
    'image_extensions' => ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.jfif', '.heic', '.avif'],/*

    |--------------------------------------------------------------------------
    | TipTap settings for the RichText field
    |--------------------------------------------------------------------------
    |
    | Load extensions for RichText fields. TipTapFigure overrides the default
    | rendering of images, wrapping them in <figure> tags and displaying a
    | caption when a title is added in the image meta.
    |
    | Figures can use the Storyblok image service for resizing and
    | transformations
    |
    */
    'tiptap' => [
        'extensions' => [
            new \Storyblok\Tiptap\Extension\Storyblok(),
//            new \Riclep\Storyblok\Support\TipTapFigure(), // use the figure plugin instead of the default img
        ],
//        'figure-transformation' => [
//            'width' => 100,
//            'height' => 100, // set to null to preserve ratio height when scaling
//            'filters' => 'filters:quality(80)', // see: https://www.storyblok.com/docs/api/image-service
//        ]
    ],

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
    | Allow live preview links
    |--------------------------------------------------------------------------
    |
    | Links in the visual editor will be clickable and navigate to the page
    | with the Storyblok editing query string appended
    |
    */
    'live_links' => true,

    /*
    |--------------------------------------------------------------------------
    | Name of the field to be used for settings
    |--------------------------------------------------------------------------
    |
    | Set the field name to be used to store the generic settings components
    |
    */
    'settings_field' => 'settings',

    /*
    |--------------------------------------------------------------------------
    | Default date format
    |--------------------------------------------------------------------------
    |
    | Use any valid PHP date format, applied when casting DateTimes to string
    |
    */
    'date_format' => 'H:i:s j F Y',

    /*
    |--------------------------------------------------------------------------
    | How deep to go when creating page schema.org data
    |--------------------------------------------------------------------------
    |
    | As you may be nesting many blocks and linking to other stories, this
    | you may want to limit the depth of the schema.org data returned
    |
    */
    'schema_org_depth' => 5,

    /*
    |--------------------------------------------------------------------------
    | URL Denylist
    |--------------------------------------------------------------------------
    |
    | URLs that should not be processed by Storyblok. Can be exact matches or
    | regular expressions (must be wrapped in forward slashes). This is only
    | used when using the packages built-in controller.
    |
    */
    'denylist' => [
        '/^\.well-known\/.*$/', // Blocks any URL starting with ".well-known/"
        // 'another-bad-slug',
        // '/^admin\/.*$/', // Blocks any URL starting with "admin/"
        // '/^user\/\d+\/edit$/', // Blocks URLs like "user/123/edit"
        // '/\.(php|sql|exe)$/', // Blocks URLs ending with .php, .sql, or .exe
    ],
];
