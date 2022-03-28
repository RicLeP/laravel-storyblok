<?php


namespace Riclep\Storyblok\Tests\Fixtures\Fields;


use Riclep\Storyblok\Fields\Image;

class WithImage extends Image
{
	public function __toString()
	{
		if ($this->content['filename']) {
			return '<img src="' . $this->content['filename'] . ' class="' . $this->with['class'] . '">';
		}

		return '';
	}
}