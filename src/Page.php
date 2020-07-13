<?php


namespace Riclep\Storyblok;

use Exception;
use Carbon\Carbon;
use Riclep\Storyblok\Traits\HasChildClasses;
use Riclep\Storyblok\Traits\HasMeta;
use Riclep\Storyblok\Traits\SchemaOrg;

class Page
{
	use HasChildClasses;
	use HasMeta;
	use SchemaOrg;

	public $_componentPath = ['page'];

	private $block;
	private $story;

	public function __construct($story) {
		$this->story = $story;

		$this->preprocess();

		$this->block = $this->createBlock($this->story['content']);

		// run automatic traits
		foreach (class_uses_recursive($this) as $trait) {
			if (method_exists($this, $method = 'init' . class_basename($trait))) {
				$this->{$method}();
			}
		}
	}

	public function render() {
		return view()->first($this->views(), $this);
	}

	public function views() {
		return array_reverse($this->block()->_componentPath);
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

	private function createBlock($content) {
		$class = $this->getChildClassName('Block', $content['component']);

		return new $class($content, $this);
	}
}