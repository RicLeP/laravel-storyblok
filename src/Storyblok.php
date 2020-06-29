<?php

namespace Riclep\Storyblok;

use Illuminate\Support\Str;
use \Riclep\Storyblok\Traits\RequestsStories;

class Storyblok
{
	use RequestsStories;

	public $page;

	public function read()
	{
		$pageClass = $this->getPageClass($this->storyblokResponse['content']['component']);
		$this->page = new $pageClass($this->storyblokResponse);

		return $this->page->preprocess()->process()->getBlocks()->postProcess();
	}

	private function getPageClass($component)
	{
		if (class_exists(config('storyblok.component_class_namespace') . 'Pages\\' . Str::studly($component))) {
			return config('storyblok.component_class_namespace') . 'Pages\\' . Str::studly($component);
		}

		return config('storyblok.component_class_namespace') . 'DefaultPage';
	}
}
