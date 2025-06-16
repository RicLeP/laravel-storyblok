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

        $data = $request->input('data');

        if (!isset($data['story'])) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json(['message' => 'Story not found'], 404));
        }

        config(['storyblok.edit_mode' => true]);

		$page = Storyblok::setData($data['story'])->render();

        // fix space removal from tags like <b>, <i> until a suitable replacement for HTML5dom has been found
        $htmlTags = config('storyblok.live_html_tags_spaces', ['a', 'b', 'i', 'strike', 'u']);
        if (is_array($htmlTags)) {
            foreach ($htmlTags as $tag) {
                // Add space before the opening tag if not already preceded by a space
                $page = preg_replace("/(?<!\s)<$tag\b/", " <$tag", $page);

                // Add space after the closing tag if not already followed by a space
                $page = preg_replace("/<\/$tag>(?!\s)/", "</$tag> ", $page);
            }
        }

		$dom = new HTML5DOMDocument();
        $dom->loadHTML($page, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);

		return $dom->querySelector(config('storyblok.live_element'))->innerHTML;
	}
}
