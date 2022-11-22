<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;

/**
 * @property false|string filename
 */
class Asset extends Field
{
	public function __construct($content, $block)
	{
		parent::__construct($content, $block);

		if (isset($this->content['filename'])) {
			$this->content['filename'] = str_replace('a.storyblok.com', config('storyblok.asset_domain'), $this->content['filename']);
        }
	}

	public function __toString(): string
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
	public function hasFile(): bool
	{
		if (!array_key_exists('filename', $this->content)) {
			return false;
		}

		return (bool) $this->content['filename'];
	}
}
