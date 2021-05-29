<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\Traits\HasChildClasses;
use Storyblok\Client;

abstract class Folder
{
	use HasChildClasses;

	/**
	 * @var bool should we request the start / index page
	 */
	protected $startPage = false;


	/**
	 * @var int Current pagination page
	 */
	protected $currentPage = 0;


	/**
	 * @var int number of items to return
	 */
	protected $perPage = 10;


	/**
	 * @var string order to sort the returned stories
	 */
	protected $sortBy = 'content.date:desc';


	/**
	 * @var string the slug to start te request from
	 */
	protected $slug;


	/**
	 * @var array additional settings for the request
	 */
	protected $settings = [];

	/**
	 * Reads a content of the returned stories, processing each one
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function read() {
		$response = $this->get();

		$stories = collect($response);

		$stories->transform(function ($story) {
			$blockClass = $this->getChildClassName('Page', $story['content']['component']);

			return new $blockClass($story);
		});

		return $stories;
	}

	/**
	 * Sets the slug of the folder to request
	 *
	 * @param $slug
	 */
	public function slug($slug) {
		$this->slug = $slug;
	}

	/**
	 * The order in which we want the items in the response to be returned
	 *
	 * @param $sortBy
	 */
	public function sort($sortBy) {
		$this->sortBy = $sortBy;
	}

	/**
	 * Define the settings for the API call
	 *
	 * @param $settings
	 */
	public function settings($settings) {
		$this->settings = $settings;
	}


	/**
	 * Caches the response and returns just the bit we want
	 *
	 * @return array
	 */
	protected function get()
	{
		if (request()->has('_storyblok') || !config('storyblok.cache')) {
			$response = $this->makeRequest();
		} else {
			$unique_tag = md5(serialize( $this->settings ));

			$response = Cache::remember('folder-' . $this->slug . '-' . $unique_tag, config('storyblok.cache_duration') * 60, function () {
				return $this->makeRequest();
			});
		}

		return $response['stories'];
	}

	/**
	 * Makes the actual request
	 *
	 * @return array|\Storyblok\stdClass
	 */
	private function makeRequest() {
		$storyblokClient = resolve('Storyblok\Client');

		$storyblokClient =  $storyblokClient->getStories(array_merge([
			'is_startpage' => $this->startPage,
			'sort_by' => $this->sortBy,
			'starts_with' => $this->slug,
			'page' => $this->currentPage,
			'per_page' => $this->perPage,
		], $this->settings));

		return $storyblokClient->getBody();
	}
}