<?php

namespace Riclep\Storyblok\Support\ImageTransformers;

use Riclep\Storyblok\Fields\Image;

abstract class BaseTransformer
{
	protected $meta = [];
	protected $transformations = [];
	protected Image $image;

	public function __construct(Image $image) {
		$this->image = $image;

		if (method_exists('preprocess', $this)) {
			$this->preprocess();
		}
	}

	public function init()
	{
		$this->extractMetaDetails();

		return $this;
	}


	protected function setMime($extension) {
		return $extension === 'jpg' ? 'image/jpeg' : 'image/' . $extension;
	}


	public function width($original = false) {
		if ($original) {
			return $this->meta['width'];
		}

		return $this->transformations['width'] ?? $this->meta['width'];
	}

	public function height($original = false) {
		if ($original) {
			return $this->meta['height'];
		}

		return $this->transformations['height'] ?? $this->meta['height'];
	}

	public function mime($original = false) {
		if ($original) {
			return $this->meta['mime'];
		}

		return $this->transformations['mime'] ?? $this->meta['mime'];
	}


	public function __toString() {
		return $this->buildUrl();
	}
}