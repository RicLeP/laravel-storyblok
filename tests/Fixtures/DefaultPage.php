<?php


namespace Riclep\Storyblok\Tests\Fixtures;

use Riclep\Storyblok\Page;
use Spatie\SchemaOrg\Schema;

class DefaultPage extends Page
{
	public function schemaOrg() {
		$localBusiness = Schema::localBusiness()
			->name('Spatie')
			->email('info@spatie.be')
			->contactPoint(Schema::contactPoint()->areaServed('Worldwide'));

		$this->schemaOrg[] = $localBusiness;
	}
}