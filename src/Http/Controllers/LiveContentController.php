<?php

namespace Riclep\Storyblok\Http\Controllers;

use IvoPetkov\HTML5DOMDocument;
use Riclep\Storyblok\StoryblokFacade as StoryBlok;
use Illuminate\Http\Request;

class LiveContentController
{
	/**
	 * Used for the live reloading on content in the visual editor. We extract the HTML inside the
	 * live_element class and replace the existing DOM elements with the new ones
	 *
	 * @param Request $request
	 * @return string
	 * @throws \Exception
	 */
	public function show(Request $request): string
    {
		config(['storyblok.edit_mode' => true]);

		$page = Storyblok::setData($request->get('data')['story'])->render();
		$dom = new HTML5DOMDocument();
		$dom->loadHTML($page);

		return $dom->querySelector(config('storyblok.live_element'))->innerHTML;
	}
}
