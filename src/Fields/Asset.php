<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;

/**
 * @property false|string filename
 */
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
		if (!array_key_exists('filename', $this->content)) {
			return false;
		}

		return (bool) $this->content['filename'];
	}
}