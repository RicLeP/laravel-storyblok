<?php


namespace Riclep\Storyblok\Tests\Fixtures\Fields;


use Riclep\Storyblok\Field;

class PersonName extends Field
{
	public function __toString()
	{
		return $this->content;
	}
}