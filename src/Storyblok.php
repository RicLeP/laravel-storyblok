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
	 * @param string $slug
	 * @param array|null $resolveRelations
	 * @param null $language
	 * @param null $fallbackLanguage
	 * @return mixed
	 * @throws ApiException
	 */
	public function read(string $slug, array $resolveRelations = null, $language = null, $fallbackLanguage = null) {
		$storyblokRequest = new RequestStory();

		if ($resolveRelations) {
			$storyblokRequest->resolveRelations($resolveRelations);
		}

		$storyblokRequest->language($language, $fallbackLanguage);

		$response = $storyblokRequest->get($slug);

		$class = $this->getChildClassName('Page', $response['content']['component']);

		return new $class($response);
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	public function setData($data): mixed
	{
		$response = $data;

		$class = $this->getChildClassName('Page', $response['content']['component']);

		return new $class($response);
	}
}
