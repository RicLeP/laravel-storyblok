<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;


use Riclep\Storyblok\Page;
use Riclep\Storyblok\Tests\Fixtures\Block;

class NullBlock extends Block
{
	public function parent(): Block|Page|null
	{
		return null;
	}

	public function page(): Block|Page|null
	{
		return null;
	}

	public function replaceMeta($key, $value): void
	{
		return;
	}
}