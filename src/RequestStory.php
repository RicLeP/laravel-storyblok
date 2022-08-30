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
	protected $resolveRelations;


	/**
	 * @var string The language version of the Story to load
	 * @see https://www.storyblok.com/docs/guide/in-depth/internationalization
	 */
	protected $language;

	/**
	 * @var string The fallback language version of the Story to load
	 * @see https://www.storyblok.com/docs/guide/in-depth/internationalization
	 */
	protected $fallbackLanguage;

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
	public function resolveRelations($resolveRelations) {
		$this->resolveRelations = implode(',', $resolveRelations);
	}

	/**
	 * Set the language and fallback language to use for this Story, will default to ‘default’
	 *
	 * @param string $language
	 * @param string|null $fallbackLanguage
	 */
	public function language($language, $fallbackLanguage = null) {
		$this->language = $language;
		$this->fallbackLanguage = $fallbackLanguage;
	}

	/**
	 * Makes the API request
	 *
	 * @param $slugOrUuid
	 * @return array|\stdClass
	 * @throws \Storyblok\ApiException
	 */
	protected function makeRequest($slugOrUuid) {
		$storyblokClient = resolve('Storyblok\Client');

		if ($this->resolveRelations) {
			$storyblokClient = $storyblokClient->resolveRelations($this->resolveRelations);
		}

		if (config('storyblok.resolve_links')) {
			$storyblokClient = $storyblokClient->resolveLinks(config('storyblok.resolve_links'));
		}

		if ($this->language) {
			$storyblokClient = $storyblokClient->language($this->language);
		}

		if ($this->fallbackLanguage) {
			$storyblokClient = $storyblokClient->fallbackLanguage($this->fallbackLanguage);
		}

		if (Str::isUuid($slugOrUuid)) {
			$storyblokClient =  $storyblokClient->getStoryByUuid($slugOrUuid);
		} else {
			$storyblokClient =  $storyblokClient->getStoryBySlug($slugOrUuid);
		}

		return $storyblokClient->getBody();
	}
}