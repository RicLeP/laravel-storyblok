<?php

namespace Riclep\Storyblok\Support;

use ArrayAccess;

class ImageTransformation implements ArrayAccess
{
	/**
	 * @var
	 */
	private array $transformation;

	/**
	 * @param array $transformation
	 */
	public function __construct(array $transformation) {
		$this->transformation = $transformation;
	}

	/**
	 * @param $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->$transformation[$offset]);
	}

	/**
	 * @param $offset
	 * @return mixed|null
	 */
	public function offsetGet($offset)
	{
		return $this->transformation[$offset] ?? null;
	}

	/**
	 * @param $offset
	 * @param $value
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->transformation[] = $value;
		} else {
			$this->transformation[$offset] = $value;
		}
	}

	/**
	 * @param $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->transformation[$offset]);
	}

	/**
	 * Allows direct access to the Image Transformer object and it’s __toString
	 *
	 * @return string
	 */
	public function __toString() {
		return (string) $this->transformation['src'];
	}
}