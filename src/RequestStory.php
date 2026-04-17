<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Storyblok\Api\Domain\Value\Dto\Version;
use Storyblok\Api\Domain\Value\Resolver\Relation;
use Storyblok\Api\Domain\Value\Resolver\RelationCollection;
use Storyblok\Api\Domain\Value\Resolver\ResolveLinks;
use Storyblok\Api\Domain\Value\Uuid;
use Storyblok\Api\Request\StoryRequest;
use Storyblok\Api\StoriesApi;

class RequestStory
{
	/**
	 * @var string|null A comma delimited string of relations to resolve matching: component_name.field_name
	 * @see https://www.storyblok.com/tp/using-relationship-resolving-to-include-other-content-entries
	 */
	protected ?string $resolveRelations = null;


	/**
	 * @var string|null The language version of the Story to load
	 * @see https://www.storyblok.com/docs/guide/in-depth/internationalization
	 */
	protected ?string $language = null;

	/**
	 * @var string|null The fallback language version of the Story to load
	 * @see https://www.storyblok.com/docs/guide/in-depth/internationalization
	 */
	protected ?string $fallbackLanguage = null;

	/**
	 * Caches the response if needed
	 *
	 * @param $slugOrUuid
	 * @return mixed
	 */
	public function get($slugOrUuid): mixed
	{
		if (request()->has('_storyblok') || !config('storyblok.cache')) {
			$response = $this->makeRequest($slugOrUuid);
		} else {
            $cache = Cache::store(config('storyblok.sb_cache_driver'));

            if ($cache instanceof Illuminate\Cache\TaggableStore) {
                $cache = $cache->tags('storyblok');
            }

            $api_hash = md5(config('storyblok.api_public_key') ?? config('storyblok.api_preview_key'));

            $response = $cache->remember($slugOrUuid . '_' . $api_hash, config('storyblok.cache_duration') * 60, function () use ($slugOrUuid) {
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
	public function resolveRelations($resolveRelations): void
	{
		$this->resolveRelations = implode(',', $resolveRelations);
	}

	/**
	 * Set the language and fallback language to use for this Story, will default to ‘default’
	 *
	 * @param string|null $language
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
	 * @return array
	 */
	private function makeRequest($slugOrUuid): array
	{
		$storyblokClient = resolve('Storyblok\Api\StoryblokClient');
		$storiesApi = new StoriesApi($storyblokClient, config('storyblok.draft') ? 'draft' : 'published');

		$withRelations = new RelationCollection();
		if ($this->resolveRelations) {
			foreach (explode(',', $this->resolveRelations) as $relation) {
				$withRelations->add(new Relation($relation));
			}
		}

		$resolveLinks = new ResolveLinks();
		if (config('storyblok.resolve_links')) {
			$resolveLinks = ResolveLinks::from(config('storyblok.resolve_links'));
		}

		$request = new StoryRequest(
			language: $this->language ?: 'default',
			version: config('storyblok.draft') ? Version::Draft : Version::Published,
			withRelations: $withRelations,
			resolveLinks: $resolveLinks,
		);

		if (Str::isUuid($slugOrUuid)) {
			$response = $storiesApi->byUuid(new Uuid($slugOrUuid), $request);
		} else {
			$response = $storiesApi->bySlug($slugOrUuid, $request);
		}

		return $response->toArray();
	}
}
