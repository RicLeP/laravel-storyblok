<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;

use PHP_Typography\Settings as TypographySettings;
use Riclep\Storyblok\xBlock;
use Riclep\Storyblok\Traits\AppliesTypography;

class Trait2 extends xBlock
{
	use AppliesTypography;

	private $applyTypography = ['typography'];

	public function init() {
		$settings = new TypographySettings();
		$settings->set_style_numbers(false);

		$this->setTypographySettings($settings);

		$this->applyTypography();
	}
}
