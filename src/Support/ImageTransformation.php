<?php

namespace Riclep\Storyblok\Support;

use ArrayAccess;

class ImageTransformation implements ArrayAccess
{

	/**
	 * @param array $transformation
	 */
	public function __construct(protected array $transformation) {
	}

	/**
	 * @param $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->$transformation[$offset]);
	}

	/**
	 * @param $offset
	 * @return mixed|null
	 */
	public function offsetGet($offset): mixed
	{
		return $this->transformation[$offset] ?? null;
	}

	/**
	 * @param $offset
	 * @param $value
	 * @return void
	 */
	public function offsetSet($offset, $value): void
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
	public function offsetUnset($offset): void
	{
		unset($this->transformation[$offset]);
	}

	/**
	 * Allows direct access to the Image Transformer object and it’s __toString
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) $this->transformation['src'];
	}
}