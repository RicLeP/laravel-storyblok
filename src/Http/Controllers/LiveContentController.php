<?php

namespace Riclep\Storyblok\Http\Controllers;

use IvoPetkov\HTML5DOMDocument;
use Riclep\Storyblok\StoryblokFacade as StoryBlok;
use Illuminate\Http\Request;

class LiveContentController
{
    public function show(Request $request, $slug = '/') {
		config(['storyblok.edit_mode' => true]);

		$page = Storyblok::setData($request->get('data')['story'])->render();
		$dom = new HTML5DOMDocument();
		$dom->loadHTML($page);

		return $dom->querySelector(config('storyblok.live_element'))->innerHTML;
	}
}
