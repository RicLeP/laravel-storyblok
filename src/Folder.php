<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\Traits\HasChildClasses;
use Storyblok\Client;

abstract class Folder
{
	use HasChildClasses;

	protected $startPage = false;
	protected $currentPage = 0;
	protected $perPage = 10;
	protected $sortBy = 'content.date:desc';
	protected $slug;
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


	protected function get()
	{
		if (request()->has('_storyblok') || !config('storyblok.cache')) {
			$response = $this->makeRequest();
		} else {
			$response = Cache::remember('folder-' . $this->slug, config('storyblok.cache_duration') * 60, function () {
				return $this->makeRequest();
			});
		}

		return $response['stories'];
	}

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