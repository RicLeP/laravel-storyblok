<?php


namespace Testcomponents\Storyblok;

use Riclep\Storyblok\Block;

class DefaultBlock extends Block
{
	public function getSubtitleAttribute() {
		return strtoupper($this->content['subtitle']);
	}
}