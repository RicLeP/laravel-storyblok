<?php


namespace Riclep\Storyblok\Tests\Fixtures;


class Block extends \Riclep\Storyblok\Block
{
	public function getTextUppercaseAttribute() {
		return strtoupper($this->text);
	}
}