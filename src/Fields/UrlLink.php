<?php


namespace Riclep\Storyblok\Fields;


class UrlLink extends Asset
{
	public function __toString(): string
	{
		return $this->cached_url;
	}
}