<?php

namespace Riclep\Storyblok\Support\ImageDrivers;

use Riclep\Storyblok\Fields\Image;
use Riclep\Storyblok\Traits\HasMeta;

abstract class BaseDriver
{
	use HasMeta;

	protected $image;
	protected $transformations = [];

	abstract protected function extractMetaDetails();

	public function init(Image $image)
	{
		$this->image = $image;

		if ($this->hasFile()) { /// has file should be in hte driver
			$this->extractMetaDetails();

			if (method_exists($this->image, 'transformations')) {
				$this->image->runTransformations();
			}
		}
	}

	public function transform($name)
	{
		if ($name) {
			if (array_key_exists($name, $this->image->transformations) ) {
				return $this->image->transformations[$name];
			}
			return false;
		}

		return $this;
	}

	public function getImage() {
		return $this->image;
	}

	public function width() {
		return $this->meta('width');
	}

	public function height() {
		return $this->meta('height');
	}

	public function mime() {
		return $this->meta('mime');
	}

	public function extension() {
		return $this->meta('extension');
	}


	/**
	 * @deprecated since version 2.7
	 * @return string
	 */
	public function type() {
		return $this->mime();
	}

	protected function setMime($extension) {
		return $extension === 'jpg' ? 'image/jpeg' : 'image/' . $extension;
	}
}