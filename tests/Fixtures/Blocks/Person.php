<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;


use Riclep\Storyblok\Tests\Fixtures\Block;
use Riclep\Storyblok\Traits\SchemaOrg;
use Spatie\SchemaOrg\Schema;

class Person extends Block
{
	use SchemaOrg;

	protected function schemaOrg() {
		return Schema::Person()
			->givenName('In the Person block')
			->email('ric@sirric.co.uk');
	}
}