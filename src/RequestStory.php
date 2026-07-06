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
use Storyblok\Api\Response\StoryResponse;
use Storyblok\Api\StoriesApi;

class RequestStory
{
    /**
     * @var array|null A comma delimited string of relations to resolve matching: component_name.field_name
     *
     * @see https://www.storyblok.com/tp/using-relationship-resolving-to-include-other-content-entries
     */
    protected ?array $resolveRelations = null;

    /**
     * @var string|null The language version of the Story to load
     *
     * @see https://www.storyblok.com/docs/guide/in-depth/internationalization
     */
    protected ?string $language = null;

    /**
     * @var string|null The fallback language version of the Story to load
     *
     * @see https://www.storyblok.com/docs/guide/in-depth/internationalization
     */
    protected ?string $fallbackLanguage = null;

    /**
     * Caches the response if needed
     */
    public function get($slugOrUuid): mixed
    {
        if (request()->has('_storyblok') || ! config('storyblok.cache')) {
            $response = $this->makeRequest($slugOrUuid)->story;
        } else {
            $cache = Cache::store(config('storyblok.sb_cache_driver'));

            if ($cache instanceof Illuminate\Cache\TaggableStore) {
                $cache = $cache->tags('storyblok');
            }

            $api_hash = md5(config('storyblok.api_public_key') ?? config('storyblok.api_preview_key'));

            $response = $cache->remember($slugOrUuid.'_'.$api_hash, config('storyblok.cache_duration') * 60, function () use ($slugOrUuid) {
                return $this->makeRequest($slugOrUuid)->story;
            });
        }

        return $response;
    }

    /**
     * Prepares the relations so the format is correct for the API call
     */
    public function resolveRelations($resolveRelations): void
    {
        $this->resolveRelations = $resolveRelations;
    }

    /**
     * Set the language and fallback language to use for this Story, will default to ‘default’
     *
     * @param  string|null  $language
     * @param  string|null  $fallbackLanguage
     */
    public function language($language, $fallbackLanguage = null)
    {
        $this->language = $language;
        $this->fallbackLanguage = $fallbackLanguage;
    }

    /**
     * Makes the API request
     */
    private function makeRequest($slugOrUuid): StoryResponse
    {
        $storyblokClient = resolve('Storyblok\Api\StoryblokClient');
        $storiesApi = new StoriesApi($storyblokClient, config('storyblok.draft') ? 'draft' : 'published');

        $withRelations = new RelationCollection;
        if ($this->resolveRelations) {
            foreach ($this->resolveRelations as $relation) {
                $withRelations->add(new Relation($relation));
            }
        }

        $resolveLinks = new ResolveLinks;
        if (config('storyblok.resolve_links')) {
            // TODO broken
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

        if ($response->rels && $this->resolveRelations) {
            $story = $response->story;

            foreach ($this->resolveRelations as $relation) {
                $relationsField = Str::of($relation)->explode('.')->last();

                if (! isset($story['content'][$relationsField]) || ! is_array($story['content'][$relationsField])) {
                    continue;
                }

                foreach ($response->rels as $relatedStory) {
                    $relatedStoryUuid = $relatedStory['uuid'] ?? null;

                    if (! $relatedStoryUuid) {
                        continue;
                    }

                    foreach ($story['content'][$relationsField] as $key => $value) {
                        if ($value === $relatedStoryUuid) {
                            $story['content'][$relationsField][$key] = $relatedStory;
                        }
                    }
                }
            }

            $response = new StoryResponse([
                'story' => $story,
                'cv' => $response->cv,
                'rels' => $response->rels,
                'rel_uuids' => $response->relUuids,
                'links' => $response->links,
            ]);
        }

        return $response;
    }
}
