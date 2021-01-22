<?php


namespace Riclep\Storyblok;

use Exception;
use Illuminate\Support\Str;
use Riclep\Storyblok\Traits\HasMeta;

abstract class Field
{
	use HasMeta;

	/**
	 * @var array|string the content of the field
	 */
	protected $content;

	/**
	 * @var Block reference to the parent block
	 */
	protected $block;

	/**
	 * Creates the new field taking it’s content and a reference
	 * to the parent Block
	 *
	 * @param $content
	 * @param $block
	 */
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