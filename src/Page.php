<?php


namespace Riclep\Storyblok;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Riclep\Storyblok\Exceptions\MissingViewException;
use Riclep\Storyblok\Traits\HasChildClasses;
use Riclep\Storyblok\Traits\HasMeta;
use Riclep\Storyblok\Traits\SchemaOrg;

class Page
{
	use HasChildClasses;
	use HasMeta;
	use SchemaOrg;

	/**
	 * @var string[] this is the root of the path so includes the page
	 */
	public $_componentPath = ['page'];

	/**
	 * @var Block root Block of the page’s content
	 */
	private $block;

	/**
	 * @var array the JSON decoded array from Storyblok
	 */
	private $story;

	/**
	 * @var array holds all the fields indexed by UUID for the JS live bridge
	 */
	public $liveContent = [];

	/**
	 * Page constructor.
	 * @param $story
	 */
	public function __construct($story) {
		$this->story = $story;

		$this->preprocess();

		$this->block = $this->createBlock($this->story['content']);

		// run automatic traits - methods matching initTraitClassName()
		foreach (class_uses_recursive($this) as $trait) {
			if (method_exists($this, $method = 'init' . class_basename($trait))) {
				$this->{$method}();
			}
		}
	}

	/**
	 * Returns a view populated with the story’s content. Additional data can
	 * be passed in using an associative array
	 *
	 * @param array $additionalContent
	 * @return View
	 */
	public function render($additionalContent = []) {
		try {
			return view()->first($this->views(), array_merge(['story' => $this], $additionalContent));
		} catch (\Exception $exception) {
			throw new MissingViewException('None of the views in the given array exist: [' . implode(', ', $this->views()) . ']');
		}
	}

	/**
	 * Returns a list of possible arrays for this page based on the Storyblok
	 * contentType component’s name
	 *
	 * @return array
	 */
	public function views() {
		$views = array_map(function($path) {
			return config('storyblok.view_path') . 'pages.' . $path;
		}, $this->block()->_componentPath);

		return array_reverse($views);
	}

	/**
	 * Returns the story for this Page
	 *
	 * @return array
	 */
	public function story() {
		return $this->story;
	}

	/**
	 * Return a lovely Carbon object of the first published date
	 *
	 * @return Carbon
	 */
	public function publishedAt() {
		return Carbon::parse($this->story['first_published_at']);
	}

	/**
	 * A Carbon object for the most recent publish date
	 *
	 * @return Carbon
	 */
	public function updatedAt() {
		return Carbon::parse($this->story['published_at']);
	}

	/**
	 * Returns the full slug for the page
	 *
	 * @return string
	 */
	public function slug() {
		return $this->story['full_slug'];
	}

	/**
	 * Returns all the tags, sorting them is so desired
	 *
	 * @param bool $alphabetical
	 * @return mixed
	 */
	public function tags($alphabetical = false) {
		if ($alphabetical) {
			sort($this->story['tag_list']);
		}

		return $this->story['tag_list'];
	}

	/**
	 * Checks if this page has the matching tag
	 *
	 * @param $tag
	 * @return bool
	 */
	public function hasTag($tag) {
		return in_array($tag, $this->tags());
	}

	/**
	 * Returns the page’s contentType Block - this is the component type
	 * used for the page in Storyblok
	 *
	 * @return Block
	 */
	public function block() {
		return $this->block;
	}

	/**
	 * Magic getter to return fields from the contentType block for this page
	 * without having to reach into the page.
	 *
	 * @param $name
	 * @return bool|string
	 */
	public function __get($name) {
		// check for accessor on the root block
		$accessor = 'get' . Str::studly($name) . 'Attribute';

		if (method_exists($this->block(), $accessor)) {
			return $this->block()->$accessor();
		}

		// check for attribute on the root block
		if ($this->block()->has($name)) {
			return $this->block()->{$name};
		}
	}

	/**
	 * Does a bit of housekeeping before processing the data
	 * from Storyblok any further
	 */
	private function preprocess() {
		// TODO extract SEO plugin
		//$this->story;

		$this->addMeta([
			'name' => $this->story['name'],
			'tags' => $this->story['tag_list'],
			'slug' => $this->story['full_slug'],
		]);
	}

	/**
	 * Creates the Block for the page’s contentType component
	 *
	 * @param $content
	 * @return mixed
	 */
	private function createBlock($content) {
		$class = $this->getChildClassName('Block', $content['component']);

		return new $class($content, $this);
	}
}