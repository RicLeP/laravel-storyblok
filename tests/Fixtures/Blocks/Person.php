<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;


use Riclep\Storyblok\Tests\Fixtures\Block;
use Riclep\Storyblok\Traits\AppliesTypography;
use Riclep\Storyblok\Traits\SchemaOrg;
use Spatie\SchemaOrg\Schema;

class Person extends Block
{
	use SchemaOrg;
	use AppliesTypography;

	protected $applyTypography = ['Text', 'Html'];

	protected function schemaOrg() {
		return Schema::Person()
			->givenName('In the Person block')
			->email('ric@sirric.co.uk');
	}
}