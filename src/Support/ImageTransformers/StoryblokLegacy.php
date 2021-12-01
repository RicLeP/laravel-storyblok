<?php

namespace Riclep\Storyblok\Support\ImageTransformers;

class StoryblokLegacy extends Storyblok
{
	/**
	 * Creates a legacy Storyblok image service URL
	 *
	 * @param $options
	 * @return string
	 */
	public function assetDomain($options = null): string
	{
		$resource = str_replace(['https:', '//' . config('storyblok.asset_domain')], '', $this->image->content()['filename']);
		return '//' . config('storyblok.image_service_domain') . $options . $resource;
	}
}