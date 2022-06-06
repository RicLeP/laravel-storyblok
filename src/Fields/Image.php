<?php


namespace Riclep\Storyblok\Fields;

use Riclep\Storyblok\Support\ImageTransformation;

class Image extends Asset
{
	/**
	 * Stores the transformations to be applied
	 *
	 * @var array
	 */
	public $transformations = [];

	/**
	 * The transformer used for this image
	 *
	 * @var mixed
	 */
	protected $transformer;

	/**
	 * The transformer class used for transformations
	 *
	 * @var mixed
	 */
	protected $transformerClass;

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

		$this->transformerClass = config('storyblok.image_transformer');

		$transformerClass = $this->transformerClass;
		$this->transformer = new $transformerClass($this);

		if (method_exists($this->transformer, 'init')) {
			$this->transformer->init();
		}

		if (method_exists($this, 'transformations')) {
			$this->transformations();
		}
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
	 * Create a new or get a transformation of the image
	 *
	 * @param $tranformation
	 * @return mixed
	 */
	public function transform($tranformation = null) {
		if ($tranformation) {
			if (array_key_exists($tranformation, $this->transformations) ) {
				return new ImageTransformation($this->transformations[$tranformation]);
			}
			return false;
		}

		$transformerClass = $this->transformerClass;
		$this->transformer = new $transformerClass($this);

		if (method_exists($this->transformer, 'init')) {
			$this->transformer->init();
		}

		return $this->transformer;
	}

	/**
	 * Set the driver to use for transformations
	 *
	 * @param $transformer
	 * @return mixed
	 */
	public function transformer($transformer) {
		$this->transformerClass = $transformer;

		return $this;
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
		return $this->picture($alt, $default, $attributes, 'laravel-storyblok::srcset');
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
	 * Reads the focus property if available and returns a string that can be used for CSS
	 * object-position or background-position. The default should be any valid value for
	 * the CSS property being used. Rigid will use hard alignments to edges.
	 *
	 * @param $default
	 * @param $rigid
	 * @return string
	 */
	public function focalPointAlignment($default = 'center', $rigid = false) {
		if (!$this->focus) {
			return $default;
		}

		preg_match_all('/\d+/', $this->focus, $matches);

		$leftPercent = round(($matches[0][0] / $this->width(true)) * 100);
		$topPercent = round(($matches[0][1] / $this->height(true)) * 100);

		if ($rigid) {
			if ($leftPercent > 66) {
				$horizontalAlignment = 'right';
			} else if ($leftPercent > 33) {
				$horizontalAlignment = 'center';
			} else {
				$horizontalAlignment = 'left';
			}

			if ($topPercent > 66) {
				$verticalAlignment = 'bottom';
			} else if ($topPercent > 33) {
				$verticalAlignment = 'center';
			} else {
				$verticalAlignment = 'top';
			}

			return $horizontalAlignment . ' ' . $verticalAlignment;
		}

		return $leftPercent . '% ' . $topPercent . '%';
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