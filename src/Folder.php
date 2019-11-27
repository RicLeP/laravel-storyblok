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
	private $folderPath;

	public function __construct($folderPath)
	{
		$this->folderPath = $folderPath;
	}


	public function read() {
		$response = $this->requestStories(resolve('Storyblok\Client'));

		$stories = collect($response->responseBody['stories']);

		$stories->transform(function ($story) {
			$blockClass = $this->getBlockClass($story['content']['component']);

			return new $blockClass($story);
		});

		dd($stories);

		return $stories;
	}

	private function requestStories(Client $storyblokClient) {

		if (request()->has('_storyblok') || !config('storyblok.cache')) {
			$response = $storyblokClient->getStories([
				'is_startpage' => $this->startPage,
				'sort_by' => 'content.publish_date:desc',
				'starts_with' => $this->folderPath,
				'page' => $this->currentPage,
				'per_page' => $this->perPage,
			]);
		} else {
			$response = Cache::remember('folder-' . $this->folderPath, config('config.cache_duration') * 60, function () use ($storyblokClient) {
				return $storyblokClient->getStories([
					'is_startpage' => $this->startPage,
					'sort_by' => 'content.publish_date:desc',
					'starts_with' => $this->folderPath,
					'page' => $this->currentPage,
					'per_page' => $this->perPage,
				]);
			});


		}

		return $response;

	}
}