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
use Riclep\Storyblok\Traits\AutoParagraphs;
use Riclep\Storyblok\Traits\ConvertsMarkdown;
use Riclep\Storyblok\Traits\ConvertsRichtext;
use Riclep\Storyblok\Traits\ProcessesBlocks;
use Riclep\Storyblok\Traits\RequestsStories;

abstract class Block implements \JsonSerializable, \Iterator, \ArrayAccess, \Countable
{
	use ProcessesBlocks;
	use RequestsStories;
	use ConvertsMarkdown;
	use ConvertsRichtext;
	use AutoParagraphs;

	public $_meta;

	protected $_componentPath = [];
	protected $_uid;
	protected $component;
	protected $content;

	private $_editable;
	private $appends;
	private $excluded;
	private $fieldtype;
	private $iteratorIndex = 0;

	/**
	 * Converts Storyblok’s API response into something usable by us. Each block becomes a class
	 * with the Storyblok UUID, the component name and any content under it’s own content key
	 *
	 * @param $block
	 */
	public function __construct($block)
	{
		if (array_key_exists('content', $block)) {
			// child story so go straight to the contents but store a few useful meta items from the Story
			$this->processStoryblokKeys($block['content']);
			$this->_meta = array_intersect_key($block, array_flip(['name', 'created_at', 'published_at', 'slug', 'full_slug']));
		} else {
			$this->processStoryblokKeys($block);
		}

		$this->content->transform(function($item, $key) {
			return $this->processBlock($item, $key);
		});

		$this->carboniseDates();

		$this->convertMarkdown();
		$this->convertRichtext();
		$this->autoParagraphs();

		if ($this->getMethods()->contains('transform')) {
			$this->transform();
		}

		if (method_exists($this, 'init')) {
			$this->init();
		}
	}

	/**
	 * Tidies up and moves a few items from the JSON response into
	 * better places for our requirements
	 *
	 * @param $block
	 */
	private function processStoryblokKeys($block) {
		$this->_uid = $block['_uid'] ?? null;
		$this->component = $block['component'] ?? null;
		$this->content = collect(array_diff_key($block, array_flip(['_editable', '_uid', 'component', 'plugin', 'fieldtype'])));
		$this->fieldtype = $block['fieldtype'] ?? null;
	}

	/**
	 * Returns the HTML comment needed to link the visual editor to
	 * the content in the view
	 *
	 * @return string
	 */
	public function editableBridge()
	{
		return $this->_editable;
	}

	/**
	 * Returns a random item from the cotent. Useful when you want to get a random item
	 * from a collection to similar Blocks such as a random banner.
	 *
	 * @return mixed
	 */
	public function random()
	{
		return $this->content->random();
	}

	/**
	 * Return the content Collection
	 *
	 * @return mixed
	 */
	public function content()
	{
		return $this->content;
	}

	/**
	 * Returns the name of the component
	 *
	 * @return string
	 */
	public function component()
	{
		return $this->component;
	}

	/**
	 * Loops over all the components an gets an array of their names in the order
	 * that they have been nested
	 *
	 * @param $componentPath
	 */
	public function makeComponentPath($componentPath)
	{
		$componentPath[] = $this->component();

		$this->_componentPath = $componentPath;

		// loop over all child classes, pass down current component list
		$this->content->each(function($block) use ($componentPath) {
			if ($block instanceof Block || $block instanceof Asset) {
				$block->makeComponentPath($componentPath);
			}
		});
	}


	/**
	 * Checks if the component has particular children
	 *
	 * @param $componentName
	 * @return mixed
	 */
	public function hasChildComponent($componentName) {
		return $this->content->filter(function ($block) use ($componentName) {
			return $block->component === $componentName;
		});
	}

	/**
	 * Returns the component’s path
	 *
	 * @return array
	 */
	public function componentPath()
	{
		return $this->_componentPath;
	}

	/**
	 * Returns a component X generations previous
	 *
	 * @param $generation
	 * @return mixed
	 */
	public function getAncestorComponent($generation)
	{
		return $this->_componentPath[count($this->_componentPath) - ($generation + 1)];
	}

	/**
	 * Checks if the current component is a child of another
	 *
	 * @param $parent
	 * @return bool
	 */
	public function isChildOf($parent)
	{
		return $this->_componentPath[count($this->_componentPath) - 2] === $parent;
	}

	/**
	 * Checks if the component is an ancestor of another
	 *
	 * @param $parent
	 * @return bool
	 */
	public function isAncestorOf($parent)
	{
		return in_array($parent, $this->_componentPath);
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
	 * Returns the UUID of the current component
	 *
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

	/**
	 * Converts date fields to carbon
	 */
	protected function carboniseDates() {
		$properties = get_object_vars($this);

		if (array_key_exists('dates', $properties)) {
			foreach ($properties['dates'] as $date) {
				if ($this->content->has($date)) {
					$this->content[$date] = Carbon::parse($this->content[$date]) ?: $this->content[$date];
				}
			}
		}
	}

	/**
	 * Do we have content
	 *
	 * @return mixed
	 */
	public function isEmpty() {
		return $this->content->isEmpty();
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


	/*
	 * Countable trait
	 * */
	public function count()
	{
		return $this->content->count();
	}

}