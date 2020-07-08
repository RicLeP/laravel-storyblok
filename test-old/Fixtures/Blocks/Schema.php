<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;

use Riclep\Storyblok\xBlock;
use Riclep\Storyblok\Traits\SchemaOrg;

class Schema extends xBlock
{
	use SchemaOrg;

	protected function schemaOrg() {
		return \Spatie\SchemaOrg\Schema::localBusiness()
			->name('In the schema block')
			->email('ric@sirric.co.uk');
	}
}