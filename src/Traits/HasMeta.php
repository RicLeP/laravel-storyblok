<?php


namespace Riclep\Storyblok\Traits;


trait HasMeta
{
	protected $_meta = [];

	public function meta() {
		return $this->_meta;
	}

	public function addMeta($fields) {
		$this->_meta = array_merge($this->_meta, $fields);
	}

	public function replaceMeta($key, $value) {
		$this->_meta[$key] = $value;
	}
}