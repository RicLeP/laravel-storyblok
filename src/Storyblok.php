<?php

namespace Riclep\Storyblok;

use Illuminate\Support\Str;
use \Riclep\Storyblok\Traits\RequestsStories;

class Storyblok
{
	use RequestsStories;

	public $page;

	/**
	 * Read, process and return the current Page
	 *
	 * @return mixed
	 */
	public function read()
	{
		$pageClass = $this->getPageClass($this->storyblokResponse['content']['component']);
		$this->page = new $pageClass($this->storyblokResponse);

		return $this->page->preprocess()->getBlocks()->postProcess();
	}

	/**
	 * Determine which Class to use for the current page
	 *
	 * @param $component
	 * @return string
	 */
	private function getPageClass($component)
	{
		if (class_exists(config('storyblok.component_class_namespace') . 'Pages\\' . Str::studly($component))) {
			return config('storyblok.component_class_namespace') . 'Pages\\' . Str::studly($component);
		}

		return config('storyblok.component_class_namespace') . 'DefaultPage';
	}
}
