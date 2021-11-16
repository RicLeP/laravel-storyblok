<?php


namespace Riclep\Storyblok\Fields;

class Image extends Asset
{
	/**
	 * Stores the transformations to be applied
	 *
	 * @var array
	 */
	public $transformations = [];

	/**
	 * The transformer class used for this image
	 *
	 * @var mixed
	 */
	protected $transformer;

	/**
	 * Constructs the image image field
	 *
	 * @param $content
	 * @param $block
	 */
	public function __construct($content, $block)
	{
		if (is_string($content)) {
			$this->upgradeStringFields($content);
			parent::__construct($this->content, $block);
		} else {
			parent::__construct($content, $block);
		}

		if (method_exists($this, 'transformations')) {
			$this->transformations();
		}

		$transformerClass = config('storyblok.image_transformer');
		$this->transformer = new $transformerClass($this);
		$this->transformer->init();
	}

	/**
	 * Get the width of the image or transformed image
	 *
	 * @param $original
	 * @return mixed
	 */
	public function width($original = false) {
		return $this->transformer->width($original);
	}

	/**
	 * Get the height of the image or transformed image
	 *
	 * @param $original
	 * @return mixed
	 */
	public function height($original = false) {
		return $this->transformer->height($original);
	}

	/**
	 * Get the mime of the image or transformed image
	 *
	 * @param $original
	 * @return mixed
	 */
	public function mime($original = false) {
		return $this->transformer->mime($original);
	}

	/**
	 * Create a new transformation on the image
	 *
	 * @param $transformer
	 * @return mixed
	 */
	public function transform($transformer = null) {
		if ($transformer) {
			$transformerClass = $transformer;
			$driver = new $transformerClass($this);
		} else {
			$transformerClass = config('storyblok.image_transformer');
			$driver = new $transformerClass($this);
		}

		return $driver->init();
	}


	/**
	 * Returns a picture element tag for this image and
	 * ant transforms defined on the image class
	 *
	 * @param $alt
	 * @param $default
	 * @param $attributes
	 * @param $view
	 * @param $reverse
	 * @return string
	 */
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

	/**
	 * Returns an image tag with srcset attribute
	 *
	 * @param $alt
	 * @param $default
	 * @param $attributes
	 * @param $view
	 * @return string
	 */
	public function srcset($alt = '', $default = null, $attributes = [], $view = 'laravel-storyblok::srcset') {
		return $this->picture($alt, $default, $attributes, 'laravel-storyblok::srcset', true);
	}

	/**
	 * Allows setting of new transformations on this image. Optionally
	 * return a new image so the original is not mutated
	 *
	 * @param $transformations
	 * @param $mutate
	 * @return $this|Image
	 */
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

	/**
	 * Converts string fields into full image fields
	 *
	 * @param $content
	 * @return void
	 */
	protected function upgradeStringFields($content) {
		$this->content = [
			'filename' => $content,
			'alt' => null,
			'copyright' => null,
			'fieldtype' => 'asset',
			'focus' => null,
			'name' => '',
			'title' => null,
		];
	}
}