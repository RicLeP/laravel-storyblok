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
	public int $currentPage = 0;


	/**
	 * @var int the total number of stories matching the request
	 */
	public int $totalStories;


	/**
	 * @var null|Collection the collection of stories in the folder
	 */
	public ?Collection $stories;


	/**
	 * @var bool should we request the start / index page
	 */
	protected bool $startPage = false;


	/**
	 * @var int number of items to return
	 */
	protected int $perPage = 10;


	/**
	 * @var string the field to sort by
	 */
	protected string $sortBy = 'published_at';


	/**
	 * @var string order to sort the returned stories
	 */
	protected string $sortOrder = 'desc';


	/**
	 * @var string the slug to start te request from
	 */
	protected string $slug = '';


	/**
	 * @var array additional settings for the request
	 */
	protected array $settings = [];

	/**
	 * @var string key used for Laravel's cache
	 */
	protected string $cacheKey = 'folder-';

	/**
	 * @param $page
	 * @param string $pageName
	 * @return LengthAwarePaginator
	 */
	public function paginate($page = null, string $pageName = 'page'): LengthAwarePaginator
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
	public function read(): Folder
	{
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
	 * @param string $slug
	 * @return Folder
	 */
	public function slug(string $slug): Folder
	{
		$this->slug = $slug;

		return $this;
	}


	/**
	 * The field and order in which we want to sort the stories by
	 *
	 * @param string $sortBy
	 * @param string|null $sortOrder
	 * @return Folder
	 */
	public function sort(string $sortBy, string $sortOrder = null): Folder
	{
		$this->sortBy = $sortBy;

		if ($sortOrder) {
			$this->sortOrder = $sortOrder;
		}

		return $this;
	}


	/**
	 * Sort ascending
	 */
	public function asc(): Folder
	{
		$this->sortOrder = 'asc';

		return $this;
	}


	/**
	 * Sort descending
	 */
	public function desc(): Folder
	{
		$this->sortOrder = 'desc';

		return $this;
	}


	/**
	 * Define the settings for the API call
	 *
	 * @param array $settings
	 */
	public function settings(array $settings): void
	{
		$this->settings = $settings;
	}


	/**
	 * Returns the total number of stories for this page
	 *
	 * @return int
	 */
	public function count(): int
	{
		return $this->stories->count() ?? 0;
	}


	/**
	 * Sets the number of items per page
	 *
	 * @param $perPage
	 * @return Folder
	 */
	public function perPage($perPage): Folder
	{
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
			$uniqueTag = md5(serialize($this->getSettings()));

			$response = Cache::remember($this->cacheKey . $this->slug . '-' . $uniqueTag, config('storyblok.cache_duration') * 60, function () {
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
	protected function makeRequest(): array
	{
		$storyblokClient = resolve('Storyblok\Client');

		$storyblokClient =  $storyblokClient->getStories($this->getSettings());

		return [
			'headers' => $storyblokClient->getHeaders(),
			'stories' => $storyblokClient->getBody()['stories'],
		];
	}

	/**
	 * Returns the settings for the folder
	 *
	 * @return array
	 */
	protected function getSettings(): array
	{
		return array_merge([
			'is_startpage' => $this->startPage,
			'sort_by' => $this->sortBy . ':' . $this->sortOrder,
			'starts_with' => $this->slug,
			'page' => $this->currentPage,
			'per_page' => $this->perPage,
		], $this->settings);
	}

	/**
	 * Returns the Stories as an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->stories->toArray();
	}
}