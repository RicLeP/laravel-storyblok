<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;


use Riclep\Storyblok\Fields\DateTime;
use Riclep\Storyblok\Block;
use Riclep\Storyblok\Tests\Fixtures\Fields\HeroImage;

class Custom extends Block
{
	protected array $_casts = [
		'datetime' => DateTime::class,
		'image' => HeroImage::class,
	];
}