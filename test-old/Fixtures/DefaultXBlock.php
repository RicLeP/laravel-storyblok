<?php

namespace Riclep\Storyblok\Tests\Fixtures;

use Riclep\Storyblok\xBlock;
use Riclep\Storyblok\Traits\xCssClasses;

class DefaultXBlock extends xBlock
{
	use xCssClasses;

	public function getSubtitleAttribute() {
		return strtoupper($this->content['subtitle']);
	}
}