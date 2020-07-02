<?php

namespace Riclep\Storyblok\Tests\Fixtures\Pages;

use Riclep\Storyblok\Page;
use Riclep\Storyblok\Traits\SchemaOrg;

class Specific extends Page
{
	use SchemaOrg;

	protected $titleField = 'use_for_title';

	protected $descriptionField = 'use_for_description';
}