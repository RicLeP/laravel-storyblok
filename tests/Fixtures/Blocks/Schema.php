<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;

use Riclep\Storyblok\Block;
use Riclep\Storyblok\Traits\SchemaOrg;

class Schema extends Block
{
	use SchemaOrg;

	protected function schemaOrg() {
		return \Spatie\SchemaOrg\Schema::localBusiness()
			->name('In the schema block')
			->email('ric@sirric.co.uk');
	}
}