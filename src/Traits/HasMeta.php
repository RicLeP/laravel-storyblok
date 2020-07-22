<?php


namespace Riclep\Storyblok\Traits;


trait HasMeta
{
	/**
	 * @var array stores the meta items for this class
	 */
	protected $_meta = [];

	/**
	 * Returns the meta items
	 *
	 * @return array
	 */
	public function meta() {
		// TODO return single key - with default
		return $this->_meta;
	}

	/**
	 * Adds a meta item
	 *
	 * @param $fields
	 */
	public function addMeta($fields) {
		$this->_meta = array_merge($this->_meta, $fields);
	}

	/**
	 * Replaces a meta item
	 *
	 * @param $key
	 * @param $value
	 */
	public function replaceMeta($key, $value) {
		$this->_meta[$key] = $value;
	}
}