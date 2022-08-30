<?php


namespace Riclep\Storyblok;


use Riclep\Storyblok\Traits\HasChildClasses;
use Storyblok\ApiException;

class Storyblok
{
	use HasChildClasses;


	/**
	 * Reads the requested story from the API
	 *
	 * @param $slug
	 * @param null $resolveRelations
	 * @param null $language
	 * @return mixed
	 * @throws ApiException
	 */
	public function read($slug, $resolveRelations = null, $language = null, $fallbackLanguage = null) {
		$storyblokRequest = new RequestStory();

		if ($resolveRelations) {
			$storyblokRequest->resolveRelations($resolveRelations);
		}

		$storyblokRequest->language($language, $fallbackLanguage);

		$response = $storyblokRequest->get($slug);

		$class = $this->getChildClassName('Page', $response['content']['component']);

		return new $class($response);
	}

	public function setData($data) {
		$response = $data;

		$class = $this->getChildClassName('Page', $response['content']['component']);

		return new $class($response);
	}
}
