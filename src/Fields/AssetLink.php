<?php


namespace Riclep\Storyblok\Fields;


class AssetLink extends Asset
{
	public function __toString(): string
	{
		return $this->url;
	}
}