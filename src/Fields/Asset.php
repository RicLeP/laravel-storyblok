<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;

class Asset extends Field
{
	public function __toString()
	{
		return $this->content['filename'];
	}

	public function hasFile() {
		return (bool) $this->content['filename'];
	}
}