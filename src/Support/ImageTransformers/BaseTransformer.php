<?php

namespace Riclep\Storyblok\Support\ImageTransformers;

use Riclep\Storyblok\Fields\Image;

abstract class BaseTransformer
{
	/**
	 * Stores basic details about the image
	 *
	 * @var
	 */
	protected $meta = [
		'height' => null,
		'width' => null,
		'extension' => null,
		'mime' => null,
	];

	/**
	 * Stores all the transformations
	 *
	 * @var array
	 */
	protected $transformations = [];

	protected Image $image;

	/**
	 * @return string The transformed image URL
	 */
	abstract public function buildUrl();


	/**
	 * Extracts meta details for the current image such as width
	 * and height, mime and anything else of use
	 *
	 * @return null
	 */
	abstract protected function extractMetaDetails();


	/**
	 * @param Image $image
	 */
	public function __construct(Image $image) {
		$this->image = $image;

		if (method_exists('preprocess', $this)) {
			$this->preprocess();
		}
	}

	/**
	 * Performs any actions needed once the object is created
	 * and any preprocessing is completed
	 *
	 * @return $this
	 */
	public function init() {
		$this->extractMetaDetails();

		return $this;
	}

	/**
	 * Returns the width of the transformed image. Optionally you
	 * can request the original width
	 *
	 * @param $original
	 * @return int
	 */
	public function width($original = false) {
		if ($original) {
			return $this->meta['width'];
		}

		return $this->transformations['width'] ?? $this->meta['width'];
	}

	/**
	 * Returns the height of the transformed image. Optionally you
	 * can request the original height
	 *
	 * @param $original
	 * @return mixed
	 */
	public function height($original = false) {
		if ($original) {
			return $this->meta['height'];
		}

		return $this->transformations['height'] ?? $this->meta['height'];
	}

	/**
	 * Returns the mime of the transformed image. Optionally you
	 * can request the original mime
	 *
	 * @param $original
	 * @return mixed
	 */
	public function mime($original = false) {
		if ($original) {
			return $this->meta['mime'];
		}

		return $this->transformations['mime'] ?? $this->meta['mime'];
	}

	/**
	 * Returns the mime from a particular file extension
	 *
	 * @param $extension
	 * @return string
	 */
	protected function setMime($extension) {
		return $extension === 'jpg' ? 'image/jpeg' : 'image/' . $extension;
	}

	/**
	 * Casts the image transformation as a sting using the
	 * buildUrl method
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->buildUrl();
	}
}