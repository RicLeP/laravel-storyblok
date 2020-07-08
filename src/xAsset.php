<?php


namespace Riclep\Storyblok;


class xAsset extends xBlock
{
	/**
	 * Checks a file has been uploaded. Storyblok will return
	 * an empty value for this field when there is no file.
	 *
	 * @return string
	 */
	public function hasFile() {
		return $this->content['filename'];
	}
}