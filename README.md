# Use Storyblok’s amazing headless CMS in way that feels familiar to Laravel developers

[![Latest Version on Packagist](https://img.shields.io/packagist/v/riclep/laravel-storyblok.svg?style=flat-square)](https://packagist.org/packages/riclep/laravel-storyblok)
[![Build Status](https://img.shields.io/travis/riclep/laravel-storyblok/master.svg?style=flat-square)](https://travis-ci.org/riclep/laravel-storyblok)
[![Quality Score](https://img.shields.io/scrutinizer/g/riclep/laravel-storyblok.svg?style=flat-square)](https://scrutinizer-ci.com/g/riclep/laravel-storyblok)
[![Total Downloads](https://img.shields.io/packagist/dt/riclep/laravel-storyblok.svg?style=flat-square)](https://packagist.org/packages/riclep/laravel-storyblok)


This package allows you to use fantastic [Storyblok headless CMS](https://www.storyblok.com/) with the amazing [Laravel PHP framework](https://laravel.com/). It’s designed to try and feel natural to Laravel developers and part of the ecosystem whilst also converting Storyblok’s API JSON responses into something powerful with minimal effort.

### Key Features

- Pages from Storyblok mapped to PHP Pages classes giving access to the nested content (Blocks) and meta data for SEO, OpenGraph and more.
- Each Storyblok component is automatically transformed into a PHP class using a simple naming convention - just match your class and component names.
- NEW! All fields in your components are converted to a Field PHP class allowing you to manipulate their data. The package automatically detects common types like rich text fields, assets and markdown.
- Asset fields are converted to Assets classes allowing you to manipulate them as required.
- Blocks and fields know where they sit in relation to their ancestors and CSS classes can be created to help your styling.
- The structure of the JSON data is preserved but super powered making it simple to loop over in your views.
- It’s simple to link to the Storyblok visual composer by including one view and calling a method for each block in your Blade.
- Request ‘Folders’ of content such as a list of articles or a team of people.
- Feels like Laravel - use date casting and accessors exactly as you would with models.
- Richer Typography with PHP Typography baked in.


## Documentation

[Read the full docs](https://ls.sirric.co.uk/docs)

## Future plans

- More transformations of content
- Better support for more components types
- Better image transformation
- Cache expensive transformations
- And more…

### Testing

The tests are mostly up-to-date and cover the majority of the code. A few areas that would require hitting the Storyblok API are not tested. If you have experience mocking API please feel free to contribute tests.

### Changelog

[See it here](CHANGELOG.md)

## Contributing

Please feel free to help expand and improve this project. Currently it supports most of the basic usage for block, fields and content. It would be great to add more advanced features and transformations or simply fix bugs.

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