<?php


namespace Riclep\Storyblok;


class Asset extends Block
{
	public function hasFile() {
		return $this->content['filename'];
	}
}