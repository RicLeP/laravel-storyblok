<?php


namespace Testcomponents\Storyblok;

use Riclep\Storyblok\Block;
use Riclep\Storyblok\Traits\AppliesTypography;

class TraitBlock extends Block
{
	use AppliesTypography;

	private $applyTypography = ['typography'];

	public function init() {
		$this->applyTypography();
	}
}