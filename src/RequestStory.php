<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RequestStory
{
	/**
	 * @var array An array of relations to resolve matching: component_name.field_name
	 * @see https://www.storyblok.com/tp/using-relationship-resolving-to-include-other-content-entries
	 */
	private $resolveRelations;

	/**
	 * Caches the response if needed
	 *
	 * @param $slugOrUuid
	 * @return mixed
	 * @throws \Storyblok\ApiException
	 */
	public function get($slugOrUuid) {
		if (request()->has('_storyblok') || !config('storyblok.cache')) {
			$response = $this->makeRequest($slugOrUuid);
		} else {
			$cache = Cache::getFacadeRoot();

			if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
				$cache = $cache->tags('storyblok');
			}

			$response = $cache->remember($slugOrUuid, config('storyblok.cache_duration') * 60, function () use ($slugOrUuid) {
				return $this->makeRequest($slugOrUuid);
			});
		}

		return $response['story'];
	}

	/**
	 * Prepares the relations so the format is correct for the API call
	 *
	 * @param $resolveRelations
	 */
	public function prepareRelations($resolveRelations) {
		$this->resolveRelations = implode(',', $resolveRelations);
	}

	/**
	 * Makes the API request
	 *
	 * @param $slugOrUuid
	 * @return array|\Storyblok\stdClass
	 * @throws \Storyblok\ApiException
	 */
	private function makeRequest($slugOrUuid) {
		$storyblokClient = resolve('Storyblok\Client');

		if ($this->resolveRelations) {
			$storyblokClient = $storyblokClient->resolveRelations($this->resolveRelations);
		}

		if (Str::isUuid($slugOrUuid)) {
			$storyblokClient =  $storyblokClient->getStoryByUuid($slugOrUuid);
		} else {
			$storyblokClient =  $storyblokClient->getStoryBySlug($slugOrUuid);
		}

		return $storyblokClient->getBody();
	}
}