<?php


namespace Riclep\Storyblok\Fields;


use Illuminate\Support\Str;
use Riclep\Storyblok\Managers\ImageTransformerManager;



class Image extends Asset
{
	public $transformations = [];

	protected $driver;

	public function __construct($content, $block)
	{
		// TODO - handle legacy image component ---- use driver instead???
		if (is_string($content)) {
			$this->upgradeOldFields($content);
			parent::__construct($this->content, $block);
		} else {
			parent::__construct($content, $block);
		}

		$this->driver = new ImageTransformerManager(app());
		$this->driver->init($this);
	}

	public function transform($name = null) {
		return $this->driver->transform($name);
	}

	public function width() {
		return $this->driver->width();
	}

	public function height() {
		return $this->driver->height();
	}

	public function mime() {
		return $this->driver->mime();
	}

	public function extension() {
		return $this->driver->extension();
	}


	/**
	 * @deprecated since version 2.7
	 * @return mixed
	 */
	public function type() {
		return $this->driver->mime();
	}

	public function meta($key = null, $default = null) {
		if ($key) {
			if (array_key_exists($key, $this->driver->meta())) {
				return $this->driver->meta($key);
			}

			return $default;
		}

		return $this->driver->meta();
	}

	public function setTransformations($transformations) {
		$this->transformations = $transformations;

		return $this;
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

	// in driver
	public function srcset($alt = '', $default = null, $attributes = [], $view = 'laravel-storyblok::srcset') {
		return $this->picture($alt, $default, $attributes, 'laravel-storyblok::srcset', true);
	}

	public function cssVars() {
		if ($this->transformations) {
			$vars = '';

			foreach ($this->transformations as $key => $transformation) {
				if (Str::endsWith($this->filename, 'svg')) {
					$vars .= '--' . $key . ': url("' . $this->filename . '"); ';
				} else {
					$vars .= '--' . $key . ': url("https:' . (string) $transformation['src'] . '"); ';
				}
			}

			return $vars;
		}

		return false;
	}

	protected function getOriginalFilenameAttribute() {
		return $this->content['filename'];
	}

	public function runTransformations() {
		$this->transformations();
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