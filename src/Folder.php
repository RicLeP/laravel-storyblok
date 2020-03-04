<?php


namespace Riclep\Storyblok;


use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\Traits\ProcessesBlocks;
use Storyblok\Client;

abstract class Folder
{
	use ProcessesBlocks;

	protected $startPage = false;
	protected $currentPage = 1;
	protected $perPage = 5;
	protected $sortBy = 'published_at:asc';
	private $slug;
	private $settings = [];

	public function read() {
		$response = $this->requestStories(resolve('Storyblok\Client'));

		$stories = collect($response->responseBody['stories']);

		$stories->transform(function ($story) {
			$blockClass = $this->getBlockClass($story['content']['component']);

			return new $blockClass($story);
		});

		return $stories;
	}

	public function slug($slug) {
		$this->slug = $slug;
	}

	public function sort($sortBy) {
		$this->sortBy = $sortBy;
	}

	public function settings($settings) {
		$this->settings = $settings;
	}

	private function requestStories(Client $storyblokClient) {

		if (request()->has('_storyblok') || !config('storyblok.cache')) {
			$response = $storyblokClient->getStories(array_merge([
				'is_startpage' => $this->startPage,
				'sort_by' => $this->sortBy,
				'starts_with' => $this->slug,
				'page' => $this->currentPage,
				'per_page' => $this->perPage,
			], $this->settings));
		} else {
			$response = Cache::remember('folder-' . $this->slug, config('config.cache_duration') * 60, function () use ($storyblokClient) {
				return $storyblokClient->getStories(array_merge([
					'is_startpage' => $this->startPage,
					'sort_by' => $this->sortBy,
					'starts_with' => $this->slug,
					'page' => $this->currentPage,
					'per_page' => $this->perPage,
				], $this->settings));
			});
		}

		return $response;
	}
}