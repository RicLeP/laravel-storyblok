<?php


namespace Riclep\Storyblok;

use Exception;
use Illuminate\Support\Str;

abstract class Field
{
	protected $content;
	protected $block;

	public function __construct($content, $block)
	{
		$this->content = $content;
		$this->block = $block;

		if (method_exists($this, 'init')) {
			$this->init();
		}
	}

	public function content() {
		return $this->content;
	}

	public function block() {
		return $this->block;
	}

	public function has($key) {
		return array_key_exists($key, $this->content);
	}

	public function __get($key) {
		$accessor = 'get' . Str::studly($key) . 'Attribute';

		if (method_exists($this, $accessor)) {
			return $this->$accessor();
		}

		try {
			if ($this->has($key)) {
				return $this->content[$key];
			}

			return false;
		} catch (Exception $e) {
			return 'Caught exception: ' .  $e->getMessage();
		}
	}

	abstract public function __toString();
}