<?php


namespace Riclep\Storyblok;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Page
{
	public $_componentPath = ['page'];
	protected $_meta = [];

	private $block;
	private $story;

	public function __construct($story) {
		$this->story = json_decode($story, true)['story'];

		$this->preprocess();

		$this->block = $this->getBlock($this->story['content']);
	}

	public function publishedAt() {
		return Carbon::parse($this->story['first_published_at']);
	}

	public function updatedAt() {
		return Carbon::parse($this->story['published_at']);
	}

	public function slug() {
		return $this->story['full_slug'];
	}

	public function tags($alphabetical = false) {
		if ($alphabetical) {
			sort($this->story['tag_list']);
		}

		return $this->story['tag_list'];
	}

	public function hasTag($tag) {
		return in_array($tag, $this->tags());
	}

	public function block() {
		return $this->block;
	}

	public function __get($name) {
		try {
			if ($this->block && $this->block->has($name)) {
				return $this->block->{$name};
			}

			return false;
		} catch (Exception $e) {
			return 'Caught exception: ' .  $e->getMessage();
		}
	}


	private function preprocess() {
		// TODO extract SEO plugin
		$this->story;
	}

	private function getBlockClass($content) {
		$component = $content['component'];

		if (class_exists(config('storyblok.component_class_namespace') . 'Blocks\\' . Str::studly($component))) {
			return config('storyblok.component_class_namespace') . 'Blocks\\' . Str::studly($component);
		}

		return config('storyblok.component_class_namespace') . 'Block';
	}

	private function getBlock($content) {
		$class = $this->getBlockClass($content);

		return new $class($content, $this);
	}

	// TODO methods for accessing meta data
}