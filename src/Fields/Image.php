<?php


namespace Riclep\Storyblok\Fields;

class Image extends Asset
{
	public $transformations = [];

	protected $driver;

	public function __construct($content, $block)
	{
		parent::__construct($content, $block);

		if (method_exists($this, 'transformations')) {
			$this->transformations();
		}

		$driverClass = config('storyblok.image_transformer');
		$this->driver = new $driverClass($this);
		$this->driver->init();
	}

	public function width($original = false) {
		return $this->driver->width($original);
	}

	public function height($original = false) {
		return $this->driver->height($original);
	}

	public function mime($original = false) {
		return $this->driver->mime($original);
	}

	public function transform() {
		$driverClass = config('storyblok.image_transformer');
		$driver = new $driverClass($this);

		return $driver->init();
	}

	public function setContent($content) {
		$this->content = $content;
	}

// in driver - then we can use clever features of each
	public function picture($alt = '', $default = null, $attributes = [], $view = 'laravel-storyblok::picture-element', $reverse = false) {
		if ($default) {
			$imgSrc = (string) $this->transformations[$default]['src'];
		} else {
			$imgSrc = $this->filename;
		}

		// srcset seems to work the opposite way to picture elements when working out sizes
		if ($reverse) {
			$transformations = array_reverse($this->transformations);
		} else {
			$transformations = $this->transformations;
		}

		return view($view, [
			'alt' => $alt,
			'attributes' => $attributes,
			'default' => $default,
			'imgSrc' => $imgSrc,
			'transformations' => $transformations,
		])->render();
	}

	public function srcset($alt = '', $default = null, $attributes = [], $view = 'laravel-storyblok::srcset') {
		return $this->picture($alt, $default, $attributes, 'laravel-storyblok::srcset', true);
	}

	public function setTransformations($transformations, $mutate = true) {
		if ($mutate) {
			$this->transformations = $transformations;

			return $this;
		}

		$class = get_class($this); // don’t mutate original object
		$image = new $class($this->content, $this->block);
		$image->transformations = $transformations;

		return $image;
	}
}