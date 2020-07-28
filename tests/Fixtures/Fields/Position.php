<?php


namespace Riclep\Storyblok\Tests\Fixtures\Fields;


use Riclep\Storyblok\Field;

class Position extends Field
{
	public function __toString()
	{
		return $this->content;
	}
}