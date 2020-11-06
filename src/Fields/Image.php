<?php


namespace Riclep\Storyblok\Fields;



use Riclep\Storyblok\Support\ImageTransformation;

class Image extends Asset
{
	// TODO add array of breakpoints to auto generate files and picture tag?

	protected $transformations = [];

	public function __construct($content, $block)
	{
		parent::__construct($content, $block);

		$this->extractMetaDetails();

		if (method_exists($this, 'transformations')) {
			$this->transformations();
		}
	}

	public function transform($name = null) {
		if ($name) {
			if (array_key_exists($name, $this->transformations) ) {
				return $this->transformations[$name];
			}
			return false; /////todo return a default image or transform?
		} else {
			return new ImageTransformation($this);
		}
	}

	public function width() {
		return $this->meta('width');
	}

	public function height() {
		return $this->meta('height');
	}

	public function type() {
		$extension = $this->meta('extension');

		if ($extension === 'jpg') {
			$extension = 'jpeg';
		}

		return 'image/' . $extension;
	}

	//TODO default, alt text (use field, or pass in string)
	public function picture($alt = '', $default = null, $attributes = [], $view = 'laravel-storyblok::picture-element') {
		if ($default) {
			$defaultSrc = (string) $this->transformations[$default]['src'];
		} else {
			$defaultSrc = $this->filename;
		}

		return view($view, [
			'alt' => $alt,
			'attributes' => $attributes,
			'default' => $default,
			'defaultSrc' => $defaultSrc,
			'transformations' => $this->transformations,
		])->render();
	}

	protected function getOriginalFilenameAttribute() {
		return $this->content['filename'];
	}

	protected function extractMetaDetails() {
		$path = $this->content['filename'];

		preg_match_all('/(?<width>\d+)x(?<height>\d+).+\.(?<extension>[a-z]{3,4})/m', $path, $dimensions, PREG_SET_ORDER, 0);

		$this->addMeta([
			'height' => $dimensions[0]['height'],
			'width' => $dimensions[0]['width'],
			'extension' => $dimensions[0]['extension'],
		]);
	}
}