<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;

class Asset extends Field
{
	public function __toString()
	{
		if ($this->content['filename']) {
			return $this->content['filename'];
		}

		return '';
	}

	/**
	 * Checks a file was uploaded
	 *
	 * @return bool
	 */
	public function hasFile() {
		return (bool) $this->content['filename'];
	}
}