<?php


namespace Riclep\Storyblok;

use Exception;
use Illuminate\Support\Str;
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

	public $_autoResolveRelations = false;
	public $_componentPath = [];
	private $_fields;
	private $_parent;

	public function __construct($content, $parent)
	{
		$this->_parent = $parent;

		$this->preprocess($content);
		$this->_componentPath = array_merge($parent->_componentPath, [Str::lower($this->meta()['component'])]);

		$this->processFields();

		// run automatic traits
		foreach (class_uses_recursive($this) as $trait) {
			if (method_exists($this, $method = 'init' . class_basename($trait))) {
				$this->{$method}();
			}
		}
	}

	public function content() {
		return $this->_fields;
	}

	public function has($key) {
		return $this->_fields->has($key);
	}

	public function parent() {
		return $this->_parent;
	}

	public function page() {
		if ($this->parent() instanceof Page) {
			return $this->parent();
		}

		return $this->parent()->page();
	}

	public function render() {
		return view()->first($this->views(), $this->content());
	}

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
	 * @param $generation
	 * @return mixed
	 */
	public function ancestorComponentName($generation)
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
		return in_array($parent, $this->parent()->_componentPath);
	}

	public function component() {
		return $this->_meta['component'];
	}

	public function editLink() {
		return $this->_meta['_editable'] ??= '';
	}


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

	private function processFields() {
		$this->_fields->transform(function ($field, $key) {
			return $this->getFieldType($field, $key);
		});
	}

	private function getFieldType($field, $key) {
		// does the block assign any $casts?
		if (property_exists($this, 'casts') && array_key_exists($key, $this->casts)) {
			return new $this->casts[$key]($field, $this);
		}

		// find fields specific to this block - BlockNameFieldName
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

	// TODO process old asset fields
	// TODO option to convert all text fields to a class - single or multiline?
	private function arrayFieldTypes($field) {
		if (array_key_exists('linktype', $field)) {
			$class = 'Riclep\Storyblok\Fields\\' . Str::studly($field['linktype']) . 'Link';

			return new $class($field, $this);
		}

		if (array_key_exists('type', $field) && $field['type'] === 'doc') {
			return new RichText($field, $this);
		}

		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'asset') {
			if (Str::endsWith($field['filename'], ['.jpg', '.jpeg', '.png', '.gif', '.webp'])) {
				return new Image($field, $this);
			}

			return new Asset($field, $this);
		}

		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'table') {
			return new Table($field, $this);
		}

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

		// had child items
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

	private function preprocess($content) {
		$this->_fields = collect(array_diff_key($content, array_flip(['_editable', '_uid', 'component'])));

		// remove non-content keys
		$this->_meta = array_intersect_key($content, array_flip(['_editable', '_uid', 'component']));
	}

	public function getIterator() {
		return $this->_fields;
	}
}