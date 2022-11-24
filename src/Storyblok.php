<?php


namespace Riclep\Storyblok;


use Riclep\Storyblok\Traits\HasChildClasses;

class Storyblok
{
	use HasChildClasses;


	/**
	 * Reads the requested story from the API
	 *
	 * @param $slug
	 * @param array|null $resolveRelations
	 * @return mixed
	 * @throws \Storyblok\ApiException
	 */
	public function read($slug, array $resolveRelations = null) {
		$storyblokRequest = new RequestStory();

		if ($resolveRelations) {
			$storyblokRequest->prepareRelations($resolveRelations);
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
