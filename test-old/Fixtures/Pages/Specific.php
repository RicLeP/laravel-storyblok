<?php

namespace Riclep\Storyblok\Tests\Fixtures\Pages;

use Riclep\Storyblok\xPage;
use Riclep\Storyblok\Traits\SchemaOrg;
use Spatie\SchemaOrg\Schema;

class Specific extends xPage
{
	use SchemaOrg;

	protected $titleField = 'use_for_title';

	protected $descriptionField = 'use_for_description';

	protected function schemaOrg() {
		return Schema::localBusiness()
			->name('On the page')
			->email('ric@sirric.co.uk')
			->contactPoint(Schema::contactPoint()->areaServed('Worldwide'));
	}
}