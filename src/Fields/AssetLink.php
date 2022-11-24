<?php


namespace Riclep\Storyblok\Fields;

/**
 * @property-read string $url
 */
class AssetLink extends Asset
{
	public function __toString(): string
	{
		return $this->url;
	}
}