<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Storyblok\ApiException;

class RequestStory
{
	/**
	 * @var array An array of relations to resolve matching: component_name.field_name
	 * @see https://www.storyblok.com/tp/using-relationship-resolving-to-include-other-content-entries
	 */
	protected array $resolveRelations;

	/**
	 * Caches the response if needed
	 *
	 * @param $slugOrUuid
	 * @return mixed
	 * @throws ApiException
	 */
	public function get($slugOrUuid): mixed
	{
		if (request()->has('_storyblok') || !config('storyblok.cache')) {
			$response = $this->makeRequest($slugOrUuid);
		} else {
			$cache = Cache::getFacadeRoot();

			if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
				$cache = $cache->tags('storyblok');
			}

			$api_hash = md5(config('storyblok.api_public_key') ?? config('storyblok.api_preview_key'));
			$response = $cache->remember($slugOrUuid . '_' . $api_hash , config('storyblok.cache_duration') * 60, function () use ($slugOrUuid) {
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
	public function prepareRelations($resolveRelations): void
	{
		$this->resolveRelations = implode(',', $resolveRelations);
	}

	/**
	 * Makes the API request
	 *
	 * @param $slugOrUuid
	 * @return array
	 * @throws ApiException
	 */
	private function makeRequest($slugOrUuid): array
	{
		$storyblokClient = resolve('Storyblok\Client');

		if ($this->resolveRelations) {
			$storyblokClient = $storyblokClient->resolveRelations($this->resolveRelations);
		}

		if (config('storyblok.resolve_links')) {
			$storyblokClient = $storyblokClient->resolveLinks(config('storyblok.resolve_links'));
		}

		if (Str::isUuid($slugOrUuid)) {
			$storyblokClient =  $storyblokClient->getStoryByUuid($slugOrUuid);
		} else {
			$storyblokClient =  $storyblokClient->getStoryBySlug($slugOrUuid);
		}

		return $storyblokClient->getBody();
	}
}