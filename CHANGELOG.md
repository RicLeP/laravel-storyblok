# Changelog


## [1.2.0] - 2020-07-03
### Changed
- Blocks now have a `parent()` method which returns the parent Block or Page
- Blocks have a `page()` method returning the Page the block is part of
- Added Schema.org support with [Spatie’s Schema.org package](https://github.com/spatie/schema-org)
- Full Page object is now passed to views
- `title`, `meta_description` and `seo` are no longer passed to the view as the entire Page is
- Page `seo` property replaced with `_meta` property


## [1.1.3] - 2020-07-01
### Changed
- Traits on Blocks and pages can automatically initialise. 
- CSS class names are not kebab case


## [1.1.2] - 2020-06-25
### Changed
- Updated CommonMark to remove deprecated packages.
- Improvements to tests.


## [1.1.1] - 2020-06-25
### Changed
- Scrutinizer CI fixes.


## [1.1.0] - 2020-06-25
### Added
- Blocks now have a _compontentPath array which includes the current and all parent components. This enables you to work out the context of the current Block.
- New CssClassses trait that can be used to generated css classes for the current Block, the layout it’s in, it’s parent Blocks etc.


## [1.0.2] - 2020-06-17
### Added
- Initial support for the new Asset fieldtype in Storyblok.


## [1.0.1] - 2020-05-18
### Changed
- Block $meta renamed $_meta.


## [1.0.0] - 2020-05-04
Initial release





https://keepachangelog.com/en/1.0.0/