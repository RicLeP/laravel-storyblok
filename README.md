# Use Storyblok’s amazing headless CMS in way that feel familiar to Laravel developers
## This is Work In Progress and not production ready!

[![Latest Version on Packagist](https://img.shields.io/packagist/v/riclep/laravel-storyblok.svg?style=flat-square)](https://packagist.org/packages/riclep/laravel-storyblok)
[![Build Status](https://img.shields.io/travis/riclep/laravel-storyblok/master.svg?style=flat-square)](https://travis-ci.org/riclep/laravel-storyblok)
[![Quality Score](https://img.shields.io/scrutinizer/g/riclep/laravel-storyblok.svg?style=flat-square)](https://scrutinizer-ci.com/g/riclep/laravel-storyblok)
[![Total Downloads](https://img.shields.io/packagist/dt/riclep/laravel-storyblok.svg?style=flat-square)](https://packagist.org/packages/riclep/laravel-storyblok)

This package allows you to use fantastic [Storyblok](https://www.storyblok.com/) headless CMS with the amazing [Laravel](https://laravel.com/) PHP framework.

It’s designed to try and feel natural to Laravel developers and part of the ecosystem whilst also automatically converting Storyblok’s API JSON responses into something powerful with minimal effort.

Each Component within Storyblok is automatically converted into a ‘Block’ class by the package and keep their original order and nesting levels. If you have a Component in StoryBlok called Foo and a matching Block class called FooBlock this will be instantiated instead of the default Block allowing you to easily override and extend behaviour on a per-Component basis.

The concept of Block classes are loosely based Laravel’s Eloquent Models. Accessing the data should feel familiar with content properties and nested child Blocks accessible simply with arrow syntax similar to how you’d work with Eloquent relations. They implement the Iterator and ArrayAccess interfaces meaning you can loop over Collections of sibling Blocks in Blade and directly access the actual content created in Storyblok.

Blocks have mutators similar to those in Eloquent allowing you to transform the output of content within your Laravel application.

Date casting is supported in the same manner as Eloquent using a $dates property on the Block.

As this package is designed to help you work with beautiful content is can also apply typographic fixes using [PHP Typography](https://github.com/mundschenk-at/php-typography). It comes with sensible default settings that can be applied to content using the $applyTypography property on a Block.

It is planned to allow automatic transforms of content when Blocks are initialised if more processing is required. These transforms could then be cached.

You can use the content in Blade anyway you like but each Block has a render() method which returns the HTML for just this Block. It will find Blade views automatically by looking for files matching component’s name and walk down a list of folders matching the URL path of the requested page enabling per page variations. This behaviour can be changed by overriding the render() method on the block.

It’s still very much work in progress with dummy/test code, comments etc. and features being added or changed at any time so use at your own risk.

Full documentation is in the works....

## Installation

You can install the package via composer:

```bash
composer require riclep/laravel-storyblok
```

## Usage

After installing the package update your .env file as so:

```
STORYBLOK_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxx
STORYBLOK_DEBUG=true #currently doesn’t do much
```

Publish the default Page and Block classes to `app/Storyblok` and package config file.

``` php
php artisan vendor:publish
```

Next update `routes/web.php` with this following catch-all route or implement your own. If using the catch-all this should be your last route to stop it intercepting any other requests in your application.

``` php
/*
 * Matches any path so should be the last route specified
 */
Route::get('/{slug?}', '\Riclep\Storyblok\Http\Controllers\StoryblokController@show')->where('slug', '(.*)');
```


### Testing

The testing is a bit crude as I’ve not mocked API responses before. Tests have been run using PHPUnit in PhpStorm.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email ric@wearebwi.com instead of using the issue tracker.

## Credits

- [Richard Le Poidevin](https://github.com/riclep)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).