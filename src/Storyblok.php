<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Str;
use Riclep\Storyblok\Traits\HasChildClasses;

class Storyblok
{
	use HasChildClasses;

	public function read($slug, $resolveRelations = null) {
		$storyblokRequest = new RequestStory();

		if ($resolveRelations) {
			$storyblokRequest->resolveRelations($resolveRelations);
		}

		$response = $storyblokRequest->get($slug);

		$class = $this->getChildClassName('Page', $response['content']['component']);

		return new $class($response);
	}
}