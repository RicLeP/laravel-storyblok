<?php


namespace Riclep\Storyblok;

use Exception;
use Illuminate\Support\Str;
use Riclep\Storyblok\Fields\Asset;
use Riclep\Storyblok\Fields\MultiAsset;
use Riclep\Storyblok\Fields\RichText;
use Riclep\Storyblok\Fields\Table;
use Storyblok\Client;

class Block
{
	public $_autoResolveRelations = false;
	public $_componentPath = [];
	private $_fields;
	private $_meta;
	private $_parent;

	public function __construct($content, $parent)
	{
		$this->_parent = $parent;

		$this->preprocess($content);
		$this->_componentPath = array_merge($parent->_componentPath, [Str::lower($this->meta()['component'])]);

		$this->processFields();
	}

	public function content() {
		return $this->_fields;
	}

	public function meta() {
		return $this->_meta;
	}

	public function addMeta($fields) {
		$this->_meta = array_merge($this->_meta, $fields);
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
			return new $this->casts[$key]($field);
		}

		// auto-match Field classes
		if ($class = $this->getFieldClass($key)) {
			return new $class($field);
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

			return new $class($field);
		}

		if (array_key_exists('type', $field) && $field['type'] === 'doc') {
			return new RichText($field);
		}

		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'asset') {
			return new Asset($field);
		}

		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'table') {
			return new Table($field);
		}

		if (Str::isUuid($field[0])) {
			if ($this->_autoResolveRelations) {
				return collect($field)->transform(function ($relation) {
					$request = new RequestStory();
					$response = $request->get($relation);

					$class = $this->getBlockClass($response['content']);
					$relationClass = new $class($response['content'], $this);

					$relationClass->addMeta([
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
					$class = $this->getBlockClass($relation['content']);
					$relationClass = new $class($relation['content'], $this);

					$relationClass->addMeta([
						'published_at' => $relation['published_at'],
						'full_slug' => $relation['full_slug'],
					]);

					return $relationClass;
				});
			}

			// this field holds blocks!
			if (array_key_exists('component', $field[0])) {
				return collect($field)->transform(function ($block) {
					$class = $this->getBlockClass($block);

					return new $class($block, $this);
				});
			}

			// multi assets
			if (array_key_exists('filename', $field[0])) {
				return new MultiAsset($field);
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

	private function getBlockClass($content) {
		$component = $content['component'];

		if (class_exists(config('storyblok.component_class_namespace') . 'Blocks\\' . Str::studly($component))) {
			return config('storyblok.component_class_namespace') . 'Blocks\\' . Str::studly($component);
		}

		return config('storyblok.component_class_namespace') . 'Block';
	}

	private function getFieldClass($key) {
		if (class_exists(config('storyblok.component_class_namespace') . 'Fields\\' . Str::studly($key))) {
			return config('storyblok.component_class_namespace') . 'Fields\\' . Str::studly($key);
		}

		return false;
	}
}