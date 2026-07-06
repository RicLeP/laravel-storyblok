<?php

namespace Riclep\Storyblok\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use IvoPetkov\HTML5DOMDocument;
use Riclep\Storyblok\StoryblokFacade as StoryBlok;

class LiveContentController
{
    /**
     * Used for the live reloading on content in the visual editor. We extract the HTML inside the
     * live_element class and replace the existing DOM elements with the new ones
     *
     * @throws \Exception
     */
    public function show(Request $request): string
    {
        $data = $request->input('sbLiveData');

        if (! isset($data['story'])) {
            throw new HttpResponseException(response()->json(['message' => 'Story not found'], 404));
        }

        config(['storyblok.edit_mode' => true]);

        $page = StoryBlok::setData($data['story'])->render();

        $dom = new HTML5DOMDocument;
        $dom->loadHTML($page, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);

        return $dom->querySelector(config('storyblok.live_element'))->innerHTML;
    }
}
