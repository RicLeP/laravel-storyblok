<?php

namespace Riclep\Storyblok\Support\ImageDrivers;

use Riclep\Storyblok\Fields\Image;

abstract class BaseDriver
{
	protected $image;
	protected $transformations;

	abstract protected function extractMetaDetails();

	public function init(Image $image)
	{
		$this->image = $image;

		if ($this->hasFile()) { /// has file should be in hte driver
			$this->extractMetaDetails();

			if (method_exists($this->image, 'transformations')) {
				$this->image->getTransformations();
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
		return $this->image->transformations['width'] ?? $this->image->width();
	}

	public function height() {
		return $this->image->transformations['height'] ?? $this->image->height();
	}

	public function type() {
		if (array_key_exists('format', $this->image->transformations)) {
			return 'image/' . $this->image->transformations['format'];
		}

		return $this->image->type();
	}

}