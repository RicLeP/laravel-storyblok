<?php


namespace Riclep\Storyblok\Traits;


use Exception;
use Illuminate\Support\Facades\Cache;
use Storyblok\Client;

trait RequestsStories
{
	//public $storyblokResponse;

	public function read()
	{
		return $this->getBlocks();
	}

	public function getBlocks() {
		$this->content = $this->processBlock($this->storyblokResponse['content'], 'root');

		return $this;
	}


	/**
	 * Requests the story from the Storyblok API
	 *
	 * @param Client $storyblok
	 * @param $slugOrUuid
	 * @param bool $byUuid
	 * @return array
	 * @throws \Storyblok\ApiException
	 */
	public function requestStory(Client $storyblokClient, $slugOrUuid, $byUuid = false)
	{
		// caching should be done better than this. Maybe using a decorator to add the functionality. https://matthewdaly.co.uk/blog/2017/03/01/decorating-laravel-repositories/
		// tests if we are in the editor and bypasses the cache
		if (request()->has('_storyblok') || !config('storyblok.cache')) {
			if ($byUuid) {
				$response = $storyblokClient->getStoryByUuid($slugOrUuid)->getBody();
			} else {
				$response = $storyblokClient->getStoryBySlug($slugOrUuid)->getBody();
			}
		} else {
			if ($byUuid) {
				$response = Cache::remember($slugOrUuid, config('config.cache_duration') * 60, function () use ($storyblokClient, $slugOrUuid) {
					return $storyblokClient->getStoryByUuid($slugOrUuid)->getBody();
				});
			} else {
				$response = Cache::remember($slugOrUuid, config('config.cache_duration') * 60, function () use ($storyblokClient, $slugOrUuid) {
					return $storyblokClient->getStoryBySlug($slugOrUuid)->getBody();
				});
			}
		}

		// TODO - error handling!

		return $response['story'];
	}

	/**
	 * Gets the Story by slug
	 *
	 * @param $slug
	 * @return mixed
	 * @throws Exception
	 */
	public function bySlug($slug)
	{
		$this->storyblokResponse = $this->requestStory(resolve('Storyblok\Client'), $slug);

		return $this;
	}

	/**
	 * Gets the story by uuid
	 *
	 * @param $uuid
	 * @return mixed
	 * @throws Exception
	 */
	public function byUuid($uuid)
	{
		$this->storyblokResponse = $this->requestStory(resolve('Storyblok\Client'), $uuid, true);

		return $this;
	}

	public function childStory($uuid) {
		return $this->requestStory(resolve('Storyblok\Client'), $uuid, true);
	}
}