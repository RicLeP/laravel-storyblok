<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;


use Riclep\Storyblok\Fields\DateTime;
use Riclep\Storyblok\Block;
use Riclep\Storyblok\Tests\Fixtures\Fields\HeroImage;
use Riclep\Storyblok\Traits\HasSettings;

class Custom extends Block
{
	use HasSettings;

	protected array $_casts = [
		'datetime' => DateTime::class,
		'image' => HeroImage::class,
	];

	public function fieldsReady() {
		$this->added = 'yes';
	}
}