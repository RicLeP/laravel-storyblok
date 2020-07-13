<?php


namespace Riclep\Storyblok\Fields;


use Illuminate\Support\Str;
use Riclep\Storyblok\Field;

class MultiAsset extends Field implements \ArrayAccess
{
	public function __toString()
	{
		// TODO
		return '';
	}

	public function init() {
		if ($this->hasFiles()) {
			$this->content = collect($this->content())->transform(function ($file) {
				if (Str::endsWith($file['filename'], ['.jpg', '.jpeg', '.png', '.gif', '.webp'])) {
					return new Image($file);
				}

				return new Asset($file);
			});
		}
	}

	public function hasFiles() {
		return (bool) $this->content();
	}

	/*
	 * Methods for ArrayAccess Trait - allows us to dig straight down to the content collection
	 * when calling a key on the Object
	 * */
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->content[] = $value;
		} else {
			$this->content[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->content[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->content[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->content[$offset]) ? $this->content[$offset] : null;
	}
}