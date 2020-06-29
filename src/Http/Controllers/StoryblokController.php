<?php

namespace Riclep\Storyblok\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\StoryblokFacade as StoryBlok;

class StoryblokController
{
	/**
	 * Loads a story
	 *
	 * @param string $slug
	 * @return \Illuminate\View\View
	 * @throws \Exception
	 */
	public function show($slug = 'home')
	{
		return Storyblok::bySlug($slug)->read()->render();
	}

	/**
	 * Deletes the cached API responses
	 */
	public function destroy() {
		Cache::flush();
	}
}
