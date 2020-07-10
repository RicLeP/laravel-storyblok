<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Str;

class Storyblok
{
	// TODO resolve relations keys - at page level - array of items to pass to client
	// auto - we do it ourself with an extra API call


	public function read($slug, $resolveRelations = null) {
		$storyblokRequest = new RequestStory();

		if ($resolveRelations) {
			$storyblokRequest->resolveRelations($resolveRelations);
		}
		
		$response = $storyblokRequest->get($slug);

		$class = $this->getPageClass($response);

		return new $class($response);
	}

	private function getPageClass($story) {
		$component = $story['content']['component'];

		if (class_exists(config('storyblok.component_class_namespace') . 'Pages\\' . Str::studly($component))) {
			return config('storyblok.component_class_namespace') . 'Pages\\' . Str::studly($component);
		}

		return config('storyblok.component_class_namespace') . 'Page';
	}
}