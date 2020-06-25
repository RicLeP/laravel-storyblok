<?php

namespace Riclep\Storyblok\Tests\Fixtures;

use Riclep\Storyblok\Block;
use Riclep\Storyblok\Traits\CssClasses;

class DefaultBlock extends Block
{
	use CssClasses;

	public function getSubtitleAttribute() {
		return strtoupper($this->content['subtitle']);
	}
}