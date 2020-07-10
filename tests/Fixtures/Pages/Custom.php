<?php


namespace Riclep\Storyblok\Tests\Fixtures\Pages;


use Riclep\Storyblok\Page;
use Spatie\SchemaOrg\Schema;

class Custom extends Page
{
	protected function schemaOrg() {
		return Schema::localBusiness()
			->name('On the page')
			->email('ric@sirric.co.uk')
			->contactPoint(Schema::contactPoint()->areaServed('Worldwide'));
	}
}