<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Facades\Cache;
use Storyblok\Client;

abstract class Folder
{
	protected $startPage = false;
	protected $currentPage = 1;
	protected $perPage = 10;
	protected $sortBy = 'published_at:asc';
	protected $slug;
	protected $settings = [];

	/**
	 * Reads a content of the returned stories, processing each one
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function read() {
		$response = $this->get();

		$stories = collect($response->responseBody['stories']);

		$stories->transform(function ($story) {
			$blockClass = $this->getBlockClass($story['content']);

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
			return $this->makeRequest();
		} else {
			return $this->makeRequest();
		}
	}

	private function makeRequest() {
		$storyblokClient = resolve('Storyblok\Client');

		return $storyblokClient->getStories(array_merge([
			'is_startpage' => $this->startPage,
			'sort_by' => $this->sortBy,
			'starts_with' => $this->slug,
			'page' => $this->currentPage,
			'per_page' => $this->perPage,
		], $this->settings));
	}
}