<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;


use Riclep\Storyblok\Tests\Fixtures\Block;

class AllFields extends Block
{
	protected array $_defaults = [
		'filled_default' => 'default from class',
	];
}