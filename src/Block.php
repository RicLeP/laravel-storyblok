<?php


namespace Riclep\Storyblok;

use Exception;
use Illuminate\Support\Str;
use Riclep\Storyblok\Fields\Asset;
use Riclep\Storyblok\Fields\Blocks;
use Riclep\Storyblok\Fields\MultiAsset;
use Riclep\Storyblok\Fields\RichText;
use Riclep\Storyblok\Fields\Table;

class Block
{
	private $_fields;
	private $_meta;

	// TODO cast fields as types / classes

	public function __construct($content)
	{
		$this->preprocess($content);
		$this->processFields();
	}

	public function content() {
		return $this->_fields;
	}

	public function meta() {
		return $this->_meta;
	}

	public function has($key) {
		return $this->_fields->has($key);
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
		$this->_fields->transform(function ($field) {
			return $this->getFieldType($field);
		});
	}

	private function getFieldType($field) {
		if (is_array($field)) {
			return $this->arrayFieldTypes($field);
		}

		// strings or anything else - do nothing
		return $field;
	}

	private function arrayFieldTypes($field) {
		if (array_key_exists('linktype', $field)) {
			// todo - get specific field classes
			$class = 'Riclep\Storyblok\Fields\\' . Str::studly($field['linktype']) . 'Link';

			return new $class($field);

			////// todo no matching class to field defaults
		}

		if (array_key_exists('type', $field) && $field['type'] === 'doc') {
			// todo - get specific field class
			return new RichText($field);
		}

		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'asset') {
			// todo - get specific field class
			return new Asset($field);
		}

		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'table') {
			// todo - get specific field class
			return new Table($field);;
		}

		if (array_key_exists(0, $field) && is_array($field[0]) && array_key_exists('component', $field[0])) {
			return new Blocks($field);
		}

		if (array_key_exists(0, $field) && is_array($field[0]) && array_key_exists('filename', $field[0])) {
			// todo - get specific field class
			return new MultiAsset($field);
		}

		return $field;
	}

	private function preprocess($content) {
		$this->_fields = collect(array_diff_key($content, array_flip(['_editable', '_uid', 'component'])));

		// remove non-content keys
		$this->_meta = array_intersect_key($content, array_flip(['_editable', '_uid', 'component']));
	}

}