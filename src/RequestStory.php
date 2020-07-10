<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RequestStory
{
	private $resolveRelations;

	public function get($slugOrUuid) {
		if (request()->has('_storyblok') || !config('storyblok.cache')) {
			$response = $this->makeRequest($slugOrUuid);
		} else {
			$response = Cache::remember($slugOrUuid, config('storyblok.cache_duration') * 60, function () use ($slugOrUuid) {
				return $this->makeRequest($slugOrUuid);
			});
		}

		return $response['story'];
	}

	public function resolveRelations($resolveRelations) {
		$this->resolveRelations = implode(',', $resolveRelations);
	}

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