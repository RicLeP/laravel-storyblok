<?php


namespace Riclep\Storyblok\Traits;


trait HasMeta
{
	/**
	 * @var array stores the meta items for this class
	 */
	protected array $_meta = [];

	/**
	 * Returns the meta items
	 *
	 * @param string|null $key the key to return
	 * @param string|null $default a default if the $key is missing
	 * @return mixed
	 */
	public function meta(string $key = null, string $default = null): mixed
	{
		if ($key) {
			if (array_key_exists($key, $this->_meta)) {
				return $this->_meta[$key];
			}

			return $default;
		}

		return $this->_meta;
	}

	/**
	 * Adds items to the meta content optionally replacing existing keys
	 *
	 * @param $fields
	 */
	public function addMeta($fields, $replace = false): void
	{
		if ($replace) {
			$this->_meta = array_merge($this->_meta, $fields);
		}
		$this->_meta = array_merge($fields, $this->_meta);
	}

	/**
	 * Replaces a meta item
	 *
	 * @param $key
	 * @param $value
	 */
	public function replaceMeta($key, $value): void
	{
		$this->_meta[$key] = $value;
	}

	/**
	 * Returns the UUID of the Block
	 * @return string
	 */
	public function uuid(): string
	{
		return $this->meta('_uid');
	}
}