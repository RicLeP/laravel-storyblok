<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;

use Riclep\Storyblok\Block;
use Riclep\Storyblok\Traits\SchemaOrg;
use Spatie\SchemaOrg\Schema;

class Date extends Block
{
	use SchemaOrg;

	protected $dates = ['schedule'];

	public function schemaOrg() {
		$localBusiness = Schema::localBusiness()
			->name('Spatie')
			->email('info@spatie.be')
			->contactPoint(Schema::contactPoint()->areaServed('Worldwide'));

		dd(resolve('storyblok'));
	}
}