<?php

namespace Riclep\Storyblok\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\StoryblokFacade as StoryBlok;

class StoryblokController
{
	/**
	 * Loads a story rendering the content in the matched view.
	 *
	 * @param string $slug
	 * @return \Illuminate\View\View
	 * @throws \Exception
	 */
	public function show($slug = 'home'): \Illuminate\View\View
	{
		return Storyblok::read($slug)->render();
	}

	/**
	 * Deletes the cached API responses
	 */
	public function destroy(): void
	{
		if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
			Cache::tags('storyblok')->flush();
		} else {
			Cache::flush();
		}
	}
}
