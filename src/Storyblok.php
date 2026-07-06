<?php


namespace Riclep\Storyblok;


use Riclep\Storyblok\Traits\HasChildClasses;
use Storyblok\Api\Domain\Value\Dto\Version;
use Storyblok\Api\Domain\Value\Id;
use Storyblok\Api\Domain\Value\Uuid;
use Storyblok\Api\StoriesApi;

class Storyblok
{
	use HasChildClasses;


	/**
	 * Reads the requested story from the API
	 *
	 * @param string $slug
	 * @param array|null $resolveRelations
	 * @param string|null $language
	 * @param string|null $fallbackLanguage
	 * @return Page
	 */
	public function read(string $slug, ?array $resolveRelations = null, ?string $language = null, ?string $fallbackLanguage = null): Page
	{
        $storyblokRequest = new RequestStory();
		if ($resolveRelations) {
			$storyblokRequest->resolveRelations($resolveRelations);
		}

		if ($language) {
			$storyblokRequest->language($language, $fallbackLanguage);
		}

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
