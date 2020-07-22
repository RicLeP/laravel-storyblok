<?php


namespace Riclep\Storyblok;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Riclep\Storyblok\Fields\Asset;
use Riclep\Storyblok\Fields\Image;
use Riclep\Storyblok\Fields\MultiAsset;
use Riclep\Storyblok\Fields\RichText;
use Riclep\Storyblok\Fields\Table;
use Riclep\Storyblok\Traits\CssClasses;
use Riclep\Storyblok\Traits\HasChildClasses;
use Riclep\Storyblok\Traits\HasMeta;

class Block implements \IteratorAggregate
{
	use CssClasses;
	use HasChildClasses;
	use HasMeta;

	/**
	 * @var bool resolve UUID relations automatically
	 */
	public $_autoResolveRelations = false;


	/**
	 * @var array the path of nested components
	 */
	public $_componentPath = [];


	/**
	 * @var Collection all the fields for the Block
	 */
	private $_fields;


	/**
	 * @var Page|Block reference to the parent Block or Page
	 */
	private $_parent;

	/**
	 * Takes the Block’s content and a reference to the parent
	 * @param $content
	 * @param $parent
	 */
	public function __construct($content, $parent)
	{
		$this->_parent = $parent;

		$this->preprocess($content);
		$this->_componentPath = array_merge($parent->_componentPath, [Str::lower($this->meta()['component'])]);

		$this->processFields();

		// run automatic traits - methods matching initTraitClassName()
		foreach (class_uses_recursive($this) as $trait) {
			if (method_exists($this, $method = 'init' . class_basename($trait))) {
				$this->{$method}();
			}
		}
	}

	/**
	 * Returns the containing every field of content
	 *
	 * @return Collection
	 */
	public function content() {
		return $this->_fields;
	}

	/**
	 * Checks if the fields contain the specified key
	 *
	 * @param $key
	 * @return bool
	 */
	public function has($key) {
		return $this->_fields->has($key);
	}

	/**
	 * Returns the parent Block
	 *
	 * @return Block
	 */
	public function parent() {
		return $this->_parent;
	}

	/**
	 * Returns the page this Block belongs to
	 *
	 * @return Block
	 */
	public function page() {
		if ($this->parent() instanceof Page) {
			return $this->parent();
		}

		return $this->parent()->page();
	}

	/**
	 * Returns the first matching view, passing it the fields
	 *
	 * @return View
	 */
	public function render() {
		return view()->first($this->views(), $this->content());
	}

	/**
	 * Returns an array of possible views for the current Block based on
	 * it’s $componentPath match the component prefixed by each of it’s
	 * ancestors in turn, starting with the closest, for example:
	 *
	 * $componentPath = ['page', 'parent', 'child', 'this_block'];
	 *
	 * Becomes a list of possible views like so:
	 * ['child.this_block', 'parent.this_block', 'page.this_block'];
	 *
	 * Override this method with your custom implementation for
	 * ultimate control
	 *
	 * @return array
	 */
	public function views() {
		$compontentPath = $this->_componentPath;
		array_pop($compontentPath);

		$views = array_map(function($path) {
			return config('storyblok.view_path') . 'blocks.' . $path . '.' . $this->component();
		}, $compontentPath);

		return array_reverse($views);
	}

	/**
	 * Returns a component X generations previous
	 *
	 * @param $generation int
	 * @return mixed
	 */
	public function ancestorComponentName($generation)
	{
		return $this->_componentPath[count($this->_componentPath) - ($generation + 1)];
	}

	/**
	 * Checks if the current component is a child of another
	 *
	 * @param $parent string
	 * @return bool
	 */
	public function isChildOf($parent)
	{
		return $this->_componentPath[count($this->_componentPath) - 2] === $parent;
	}

	/**
	 * Checks if the component is an ancestor of another
	 *
	 * @param $parent string
	 * @return bool
	 */
	public function isAncestorOf($parent)
	{
		return in_array($parent, $this->parent()->_componentPath);
	}

	/**
	 * Returns the current Block’s component name from Storyblok
	 *
	 * @return string
	 */
	public function component() {
		return $this->_meta['component'];
	}


	/**
	 * Returns the HTML comment required for making this Block clickable in
	 * Storyblok’s visual editor. Don’t forget to set comments to true in
	 * your Vue.js app configuration.
	 *
	 * @return string
	 */
	public function editorLink() {
		return $this->_meta['_editable'] ??= '';
	}


	/**
	 * Magic accessor to pull content from the _fields collection. Works just like
	 * Laravel’s model accessors. Matches public methods with the follow naming
	 * convention getSomeFieldAttribute() - called via $block->some_field
	 *
	 * @param $key
	 * @return bool|string
	 */
	public function __get($key) {
		$accessor = 'get' . Str::studly($key) . 'Attribute';

		if (method_exists($this, $accessor)) {
			return $this->$accessor();
		}

		try {
			if ($this->has($key)) {
				return $this->_fields[$key];
			}

			return false;
		} catch (Exception $e) {
			return 'Caught exception: ' .  $e->getMessage();
		}
	}

	/**
	 * Loops over every field to get te ball rolling
	 */
	private function processFields() {
		$this->_fields->transform(function ($field, $key) {
			return $this->getFieldType($field, $key);
		});
	}

	/**
	 * Converts fields into Field Classes based on various properties of their content
	 *
	 * @param $field
	 * @param $key
	 * @return array|Collection|mixed|Asset|Image|MultiAsset|RichText|Table
	 */
	private function getFieldType($field, $key) {
		// TODO process old asset fields
		// TODO option to convert all text fields to a class - single or multiline?

		// does the Block assign any $casts? This is key (field) => value (class)
		if (property_exists($this, 'casts') && array_key_exists($key, $this->casts)) {
			return new $this->casts[$key]($field, $this);
		}

		// find Fields specific to this Block matching: BlockNameFieldName
		if ($class = $this->getChildClassName('Field', $this->component() . '_' . $key)) {
			return new $class($field, $this);
		}

		// auto-match Field classes
		if ($class = $this->getChildClassName('Field', $key)) {
			return new $class($field, $this);
		}

		// complex fields
		if (is_array($field) && !empty($field)) {
			return $this->arrayFieldTypes($field);
		}

		// strings or anything else - do nothing
		return $field;
	}


	/**
	 * When the field is an array we need to do more processing
	 *
	 * @param $field
	 * @return Collection|mixed|Asset|Image|MultiAsset|RichText|Table
	 */
	private function arrayFieldTypes($field) {
		// match link fields
		if (array_key_exists('linktype', $field)) {
			$class = 'Riclep\Storyblok\Fields\\' . Str::studly($field['linktype']) . 'Link';

			return new $class($field, $this);
		}

		// match rich-text fields
		if (array_key_exists('type', $field) && $field['type'] === 'doc') {
			return new RichText($field, $this);
		}

		// match asset fields - detecting raster images
		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'asset') {
			if (Str::endsWith($field['filename'], ['.jpg', '.jpeg', '.png', '.gif', '.webp'])) {
				return new Image($field, $this);
			}

			return new Asset($field, $this);
		}

		// match table fields
		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'table') {
			return new Table($field, $this);
		}

		// it’s an array of relations - request them if we’re auto resolving
		if (Str::isUuid($field[0])) {
			if ($this->_autoResolveRelations) {
				return collect($field)->transform(function ($relation) {
					$request = new RequestStory();
					$response = $request->get($relation);

					$class = $this->getChildClassName('Block', $response['content']['component']);
					$relationClass = new $class($response['content'], $this);

					$relationClass->addMeta([
						'name' => $relation['name'],
						'published_at' => $response['published_at'],
						'full_slug' => $response['full_slug'],
					]);

					return $relationClass;
				});
			}
		}

		// has child items - single option, multi option and Blocks fields
		if (is_array($field[0])) {
			// resolved relationships - entire story is returned, we just want the content and a few meta items
			if (array_key_exists('content', $field[0])) {
				return collect($field)->transform(function ($relation) {
					$class = $this->getChildClassName('Block', $relation['content']['component']);
					$relationClass = new $class($relation['content'], $this);

					$relationClass->addMeta([
						'name' => $relation['name'],
						'published_at' => $relation['published_at'],
						'full_slug' => $relation['full_slug'],
					]);

					return $relationClass;
				});
			}

			// this field holds blocks!
			if (array_key_exists('component', $field[0])) {
				return collect($field)->transform(function ($block) {
					$class = $this->getChildClassName('Block', $block['component']);

					return new $class($block, $this);
				});
			}

			// multi assets
			if (array_key_exists('filename', $field[0])) {
				return new MultiAsset($field, $this);
			}
		}

		// just return the array
		return $field;
	}

	/**
	 * Storyblok returns fields and other meta content at the same level so
	 * let’s do a little tidying up first
	 *
	 * @param $content
	 */
	private function preprocess($content) {
		$this->_fields = collect(array_diff_key($content, array_flip(['_editable', '_uid', 'component'])));

		// remove non-content keys
		$this->_meta = array_intersect_key($content, array_flip(['_editable', '_uid', 'component']));
	}

	/**
	 * Let’s up loop over the fields in Blade without needing to
	 * delve deep into the content collection
	 *
	 * @return \Traversable
	 */
	public function getIterator() {
		return $this->_fields;
	}
}