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
	 * @param null $key the key to return
	 * @param null $default a default if the $key is missing
	 * @return array
	 */
	public function meta($key = null, $default = null) {
		if ($key) {
			if (array_key_exists($key, $this->_meta)) {
				return $this->_meta[$key];
			}

			return $default;
		}

		return $this->_meta;
	}

	/**
	 * Adds items to the meta content keeping any that already exist
	 *
	 * @param $fields
	 */
	public function addMeta($fields) {
		$this->_meta = array_merge($fields, $this->_meta);
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

	/**
	 * Returns the UUID of the Block
	 * @return string
	 */
	public function uuid() {
		return $this->meta('_uid');
	}
}