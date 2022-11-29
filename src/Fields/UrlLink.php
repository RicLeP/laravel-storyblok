<?php


namespace Riclep\Storyblok\Fields;


/**
 * @property-read string $cached_url
 */
class UrlLink extends Asset
{
	public function __toString(): string
	{
		return $this->cached_url;
	}
}