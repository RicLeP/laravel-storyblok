<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Str;

class RequestStory
{
	public function get($slugOrUuid) {
		$storyblokClient = resolve('Storyblok\Client');

		if (Str::isUuid($slugOrUuid)) {
			$response = $storyblokClient->getStoryByUuid($slugOrUuid)->getBody();
		} else {
			$response = $storyblokClient->getStoryBySlug($slugOrUuid)->getBody();
		}

		return $response;
	}
}