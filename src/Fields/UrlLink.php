<?php


namespace Riclep\Storyblok\Fields;


class UrlLink extends Asset
{
	public function __toString()
	{
		return $this->cached_url;
	}
}