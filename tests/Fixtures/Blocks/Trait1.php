<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;

use Riclep\Storyblok\Block;
use Riclep\Storyblok\Traits\AppliesTypography;
use Riclep\Storyblok\Traits\ConvertsMarkdown;

class Trait1 extends Block
{
	use AppliesTypography;

	protected $autoParagraphs = ['body'];

	protected $markdown = ['markdown', 'table'];

	private $applyTypography = ['typography'];

	public function init() {
		$this->applyTypography();
	}
}