<?php


namespace Riclep\Storyblok\Fields;


class AssetLink extends Asset
{
	public function __toString()
	{
		return $this->url;
	}
}