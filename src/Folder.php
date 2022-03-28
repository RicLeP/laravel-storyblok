<?php


namespace Riclep\Storyblok;


use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\Traits\HasChildClasses;

abstract class Folder
{
	use HasChildClasses;


	/**
	 * @var int Current pagination page
	 */
	public $currentPage = 0;


	/**
	 * @var int the total number of stories matching the request
	 */
	public $totalStories;


	/**
	 * @var null|Collection the collection of stories in the folder
	 */
	public $stories;


	/**
	 * @var bool should we request the start / index page
	 */
	protected $startPage = false;


	/**
	 * @var int number of items to return
	 */
	protected $perPage;


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


	public function paginate($page = null, $pageName = 'page')
	{
		$page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

		return new LengthAwarePaginator(
			$this->stories,
			$this->totalStories,
			$this->perPage,
			$page,
			[
				'path' => LengthAwarePaginator::resolveCurrentPath(),
				'pageName' => $pageName,
			]
		);
	}


	/**
	 * Reads a content of the returned stories, processing each one
	 *
	 * @return Folder
	 */
	public function read() {
		$stories = $this->get()->transform(function ($story) {
			$blockClass = $this->getChildClassName('Page', $story['content']['component']);

			return new $blockClass($story);
		});

		$this->stories = $stories;

		return $this;
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
	 * Returns the total number of stories for this page
	 *
	 * @return int
	 */
	public function count() {
		return $this->stories->count() ?? 0;
	}


	/**
	 * Sets the number of items per page
	 *
	 * @param $perPage
	 * @return $this
	 */
	public function perPage($perPage) {
		$this->perPage = $perPage;

		return $this;
	}


	/**
	 * Caches the response and returns just the bit we want
	 *
	 * @return Collection
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

		$this->totalStories = $response['headers']['Total'][0];

		return collect($response['stories']);
	}


	/**
	 * Makes the actual request
	 *
	 * @return array
	 */
	protected function makeRequest() {
		$storyblokClient = resolve('Storyblok\Client');

		$storyblokClient =  $storyblokClient->getStories(array_merge([
			'is_startpage' => $this->startPage,
			'sort_by' => $this->sortBy,
			'starts_with' => $this->slug,
			'page' => $this->currentPage,
			'per_page' => $this->perPage,
		], $this->settings));

		return [
			'headers' => $storyblokClient->getHeaders(),
			'stories' => $storyblokClient->getBody()['stories'],
		];
	}
}