<?php


namespace Riclep\Storyblok\Fields;


use Illuminate\Support\Str;
use Riclep\Storyblok\Support\ImageTransformation;

class Image extends Asset
{
	protected $transformations = [];

	public function __construct($content, $block)
	{

		if (is_string($content)) {
			$this->upgradeOldFields($content);
			parent::__construct($this->content, $block);
		} else {
			parent::__construct($content, $block);
		}

		if ($this->hasFile()) {
			$this->extractMetaDetails();

			if (method_exists($this, 'transformations')) {
				$this->transformations();
			}
		}
	}

	public function transform($name = null) {
		if ($name) {
			if (array_key_exists($name, $this->transformations) ) {
				return $this->transformations[$name];
			}
			return false;
		}

		return new ImageTransformation($this);
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

	public function picture($alt = '', $default = null, $attributes = [], $view = 'laravel-storyblok::picture-element') {
		if ($default) {
			$imgSrc = (string) $this->transformations[$default]['src'];
		} else {
			$imgSrc = $this->filename;
		}

		return view($view, [
			'alt' => $alt,
			'attributes' => $attributes,
			'default' => $default,
			'imgSrc' => $imgSrc,
			'transformations' => $this->transformations,
		])->render();
	}

	protected function getOriginalFilenameAttribute() {
		return $this->content['filename'];
	}

	protected function extractMetaDetails() {
		$path = $this->content['filename'];

		preg_match_all('/(?<width>\d+)x(?<height>\d+).+\.(?<extension>[a-z]{3,4})/mi', $path, $dimensions, PREG_SET_ORDER, 0);

		if (Str::endsWith(strtolower($this->content['filename']), '.svg')) {
			$this->addMeta([
				'height' => false,
				'width' => false,
				'extension' => 'svg',
			]);
		} else {
			$this->addMeta([
				'height' => $dimensions[0]['height'],
				'width' => $dimensions[0]['width'],
				'extension' => strtolower($dimensions[0]['extension']),
			]);
		}
	}

	private function upgradeOldFields($content) {
		$this->content = [
			'filename' => $content,
			'alt' => null,
			'copyright' => null,
			'fieltype' => 'asset',
			'focus' => null,
			'name' => '',
			'title' => null,
		];
	}
}