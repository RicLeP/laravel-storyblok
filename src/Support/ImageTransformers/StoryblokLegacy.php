<?php

namespace Riclep\Storyblok\Support\ImageTransformers;

use Illuminate\Support\Str;

class StoryblokLegacy extends Storyblok
{
	public function assetDomain($options = null): string
	{
		$resource = str_replace(['https:', '//' . config('storyblok.asset_domain')], '', $this->image->content()['filename']);
		return '//' . config('storyblok.image_service_domain') . $options . $resource;
	}
}