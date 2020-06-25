<?php


namespace Testcomponents\Storyblok\Blocks;

use Riclep\Storyblok\Block;
use Riclep\Storyblok\Traits\AppliesTypography;

class Trait1 extends Block
{
	use AppliesTypography;

	private $applyTypography = ['typography'];

	public function init() {
		$this->applyTypography();
	}
}