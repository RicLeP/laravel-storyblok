<?php

namespace Riclep\Storyblok\Support\ImageDrivers;

use Riclep\Storyblok\Fields\Image;
use Riclep\Storyblok\Traits\HasMeta;

abstract class BaseDriver
{
	protected $meta = [];
	protected $transformations = [];
	protected Image $image;

	public function init(Image $image)
	{
		$this->image = $image;

		$this->extractMetaDetails();

		return $this;

		/*if (method_exists($this->image, 'transformations')) {
			$this->image->transformations();
		}*/

		/*if ($this->hasFile()) { /// has file should be in hte driver
			$this->extractMetaDetails();

			if (method_exists($this->image, 'transformations')) {
				$this->image->runTransformations();
			}
		}*/
	}


	protected function setMime($extension) {
		return $extension === 'jpg' ? 'image/jpeg' : 'image/' . $extension;
	}


	public function width() {
		return $this->transformations['width'] ?? $this->meta['width'];
	}

	public function height() {
		return $this->transformations['height'] ?? $this->meta['height'];
	}

	public function mime() {
		return $this->transformations['mime'] ?? $this->meta['mime'];
	}


	public function __toString() {
		return $this->buildUrl();
	}
}