<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;


use Riclep\Storyblok\Tests\Fixtures\Block;

class NullBlock extends Block
{
	public function parent()
	{
		return null;
	}

	public function page()
	{
		return null;
	}

	public function replaceMeta($key, $value)
	{
		return null;
	}
}