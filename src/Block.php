<?php

// TODO - add defaults / null object
// TODO - date casting to Carbon

/////// blocks might be keyed with numbers from storyblok.
/// we might need to be able to access specific ones - reordering content will change the number
/// we either need a method to find a specific child (by component name)
/// or a Block Trait to key content by the child component name

namespace Riclep\Storyblok;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Riclep\Storyblok\Traits\ProcessesBlocks;
use Riclep\Storyblok\Traits\RequestsStories;

abstract class Block implements \JsonSerializable, \Iterator, \ArrayAccess
{
	use ProcessesBlocks;
	use RequestsStories; // TODO cab we dynamically add this just for blocks that make requests

	protected $_uid;
	protected $component;
	protected $content;
	private $iteratorIndex = 0;

	/**
	 * Converts Storyblok’s API response into something usable by us. Each block becomes a class
	 * with the Storyblok UUID, the component name and any content under it’s own content key
	 *
	 * @param $block
	 * @param $key
	 */
	public function __construct($block, $key)
	{
		$this->processStoryblokKeys($block);

		$this->content->transform(function($item, $key) {
			return $this->processBlock($item, $key);
		});

		$this->carboniseDates();

		if ($this->getMethods()->contains('transform')) {
			$this->transform();
		}

		if (method_exists($this, 'init')) {
			$this->init();
		}
	}

	private function processStoryblokKeys($block) {
		$this->_uid = $block['_uid'] ?? null;
		$this->component = $block['component'] ?? null;
		$this->content = collect(array_diff_key($block, array_flip(['_editable', '_uid', 'component', 'plugin'])));
	}

	protected function view() {
		$views[] = 'storyblok.blocks.uuid.' . $this->_uid;
		$segments = explode('/', rtrim(app()->make('Page')->slug(), '/'));
		// creates an array of dot paths for each path segment
		// site.com/this/that/them becomes:
		// this.that.them
		// this.that
		// this
		$views[] = 'storyblok.blocks.' . implode('.', $segments) . '.=' . $this->component;
		$views[] = 'storyblok.blocks.' . implode('.', $segments) . '.' . $this->component;
		while (count($segments) > 1) {
			array_pop($segments);
			$views[] = 'storyblok.blocks.' . implode('.', $segments) . '.' . $this->component;
		}

		$views[] = 'storyblok.blocks.' . $this->component;
		$views[] = 'storyblok.blocks.default';

		return $views;
	}

	/**
	 * Finds the view used to display this block’s content
	 * it will always fall back to a default view.
	 *
	 */
	public function render()
	{
		return view()->first(
			$this->view(),
			[
				'block' => $this
			]
		);
	}

	/**
	 * @return mixed
	 */
	public function editableBridge()
	{
		// TODO - make this work
		if (!$this->slug) {
			return $this->_editable ?: null;
		}

		return null;
	}

	/**
	 * @return mixed
	 */
	public function content()
	{
		return $this->content;
	}

	/**
	 * @return mixed
	 */
	public function component()
	{
		return $this->component;
	}

	public function filterComponent($componentName) {
		return $this->content->filter(function ($block) use ($componentName) {
			return $block->component === $componentName;
		});
	}

	/**
	 * Checks if the content contains the specified item
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function has($key) {
		return $this->content->has($key);
	}

	/**
	 * @return mixed
	 */
	public function uuid()
	{
		return $this->_uid;
	}

	/**
	 * Returns a rendered version of the block’s view
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->content()->toJson(JSON_PRETTY_PRINT);
	}

	/**
	 * Determines how this object is converted to JSON
	 *
	 * @return mixed
	 */
	public function jsonSerialize()
	{
		if (property_exists($this, 'excluded')) {
			$content = $this->content->except($this->excluded);
		} else {
			$content = $this->content;
		}

		$attributes = [];

		// get the appended attributes
		if (property_exists($this, 'appends')) {
			foreach ($this->appends as $attribute) {
				$attributes[$attribute] = $this->{$attribute};
			}
		}

		return $content->merge(collect($attributes));
	}


	/**
	 * As all content sits under the content property we can ease access to these with a magic getter
	 * it looks inside the content collection for a matching key and returns it.
	 *
	 * If an accessor has been created for an existing or ‘new’ content item it will be returned.
	 *
	 * @param $name
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public function __get($name) {
		$accessor = 'get' . Str::studly($name) . 'Attribute';

		if ($this->getMethods()->contains($accessor)) {
			return $this->$accessor();
		}

		try {
			if ($this->has($name)) {
				return $this->content[$name];
			}

			return false;
		} catch (Exception $e) {
			return 'Caught exception: ' .  $e->getMessage();
		}
	}

	/**
	 * Gets all the public methods for a class and it’s descendants
	 *
	 * @return Collection
	 * @throws \ReflectionException
	 */
	public function getMethods() {
		$class = new ReflectionClass($this);
		return collect($class->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED))->transform(function($item) {
			return $item->name;
		});
	}

	protected function carboniseDates() {
		$properties = get_object_vars($this);

		if (array_key_exists('dates', $properties)) {
			foreach ($properties['dates'] as $date) {
				if ($this->content->has($date)) {
					$this->content[$date] = $this->content[$date] ? Carbon::parse($this->content[$date]) : null;
				}
			}
		}
	}

	/*
	 * Methods for Iterator trait allowing us to foreach over a collection of
	 * Blocks and return their content. This makes accessing child content
	 * in Blade much cleaner
	 * */
	public function current()
	{
		return $this->content[$this->iteratorIndex];
	}

	public function next()
	{
		$this->iteratorIndex++;
	}

	public function rewind()
	{
		$this->iteratorIndex = 0;
	}

	public function key()
	{
		return $this->iteratorIndex;
	}

	public function valid()
	{
		return isset($this->content[$this->iteratorIndex]);
	}

	/*
	 * Methods for ArrayAccess Trait - allows us to dig straight down to the content collection
	 * when calling a key on the Object
	 * */
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->content[] = $value;
		} else {
			$this->content[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->content[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->content[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->content[$offset]) ? $this->content[$offset] : null;
	}

}