# Use Storyblok’s amazing headless CMS in way that feels familiar to Laravel developers
## This is Work In Progress and not production ready!

[![Latest Version on Packagist](https://img.shields.io/packagist/v/riclep/laravel-storyblok.svg?style=flat-square)](https://packagist.org/packages/riclep/laravel-storyblok)
[![Build Status](https://img.shields.io/travis/riclep/laravel-storyblok/master.svg?style=flat-square)](https://travis-ci.org/riclep/laravel-storyblok)
[![Quality Score](https://img.shields.io/scrutinizer/g/riclep/laravel-storyblok.svg?style=flat-square)](https://scrutinizer-ci.com/g/riclep/laravel-storyblok)
[![Total Downloads](https://img.shields.io/packagist/dt/riclep/laravel-storyblok.svg?style=flat-square)](https://packagist.org/packages/riclep/laravel-storyblok)

This package allows you to use fantastic [Storyblok](https://www.storyblok.com/) headless CMS with the amazing [Laravel](https://laravel.com/) PHP framework. It’s designed to try and feel natural to Laravel developers and part of the ecosystem whilst also converting Storyblok’s API JSON responses into something powerful with minimal effort.

Each Component within Storyblok is automatically converted into a ‘Block’ class by the package keeping their original order and nesting. If you have a Component in Storyblok called `Foo` and a matching Block class called `Foo` this will be instantiated. If one can’t be found the `DefaultBlock` will be used instead. This allows you to easily override and extend behaviour on a per-Component/Block basis.

The concept of Block classes are loosely based Laravel’s Eloquent Models. Accessing the data should feel familiar with content properties and nested child Blocks accessible simply with arrow syntax similar to how you’d work with Eloquent relations. They implement the Iterator and ArrayAccess interfaces meaning you can loop over Collections of sibling Blocks in Blade and directly access the actual content created in Storyblok.

They also support familiar concepts such as date casting and accessors as well as new features such as converting Markdown or wrapping content in paragraph tags.

As this package is designed to help you work with beautiful content is can also apply typographic fixes using [PHP Typography](https://github.com/mundschenk-at/php-typography). It comes with sensible default settings that can be applied to content using the $applyTypography property on a Block.

## Documentation

[Read the full docs](https://ls.sirric.co.uk/docs)

## Future plans

- More transformations of content
- Better support for more components types
- Better image transformation
- Cache expensive transformations
- And more…

### Testing

The testing is a bit crude as I’ve not mocked API responses before. Tests have been run using PHPUnit in PhpStorm.

### Changelog

TODO

## Contributing

TODO

### Security

If you discover any security related issues, please email ric@wearebwi.com instead of using the issue tracker.

## Credits

- Richard Le Poidevin [GitHub](https://github.com/riclep) / [Twitter](https://twitter.com/riclep)
- [Storyblok](https://www.storyblok.com/)
- [Laravel](https://laravel.com/)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).