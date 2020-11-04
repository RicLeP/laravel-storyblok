<?php


namespace Riclep\Storyblok\Fields;



class Image extends Asset
{
	// TODO add methods for scaling and cropping image etc
	// TODO add array of breakpoints to auto generate files and picture tag?

	/**
	 * Stores the transformations to apply to this image
	 *
	 * @var array
	 */
	private array $_transformations = [];

	private $_hasFilter = false;

	public function __construct($content, $block)
	{
		parent::__construct($content, $block);

		$this->getDetails();
	}

	public function __toString()
	{
		if (!empty($this->_transformations)) {
			$transforms = '';

			if (array_key_exists('fit-in', $this->_transformations)) {
				$transforms .= '/fit-in';
			}

			if (array_key_exists('width', $this->_transformations)) {
				$transforms .= '/' . $this->_transformations['width'] . 'x' . $this->_transformations['height'];
			}

			if (array_key_exists('smart', $this->_transformations)) {
				$transforms .= '/smart';
			}

			// filters
			if ($this->_hasFilter) {
				$transforms .= '/filters';

				if (array_key_exists('format', $this->_transformations)) {
					$transforms .= ':format(' . $this->_transformations['format'] . ')';
				}

				if (array_key_exists('quality', $this->_transformations)) {
					$transforms .= ':quality(' . $this->_transformations['quality'] . ')';
				}

				if (array_key_exists('fill', $this->_transformations)) {
					$transforms .= ':fill(' . $this->_transformations['fill'] . ')';
				}
			}

			return $this->transform($transforms);
		}


		return 'fffff';
	}

	protected function getOriginalFilenameAttribute() {
		return $this->content['filename'];
	}

	protected function getDetails() {
		$path = $this->content['filename'];

		preg_match_all('/(?<width>\d+)x(?<height>\d+).+\.(?<extension>[a-z]{3,4})/m', $path, $dimensions, PREG_SET_ORDER, 0);

		$this->addMeta([
			'height' => $dimensions[0]['height'],
			'width' => $dimensions[0]['width'],
			'extension' => $dimensions[0]['extension'],
		]);
	}

	/// resize - store original width and update actual in meta - resized boolean
	///

	public function resize($width = 0, $height = 0, $smart = false)
	{
		$this->_transformations = array_merge($this->_transformations, [
			'width' => $width,
			'height' => $height,
		]);

		if ($smart) {
			$this->_transformations = array_merge($this->_transformations, [
				'smart' => true,
			]);
		}

		return $this;
	}

	public function format($format, $quality = null)
	{
		$this->_hasFilter = true;

		$this->_transformations = array_merge($this->_transformations, [
			'format' => $format,
		]);

		if ($quality) {
			$this->_transformations = array_merge($this->_transformations, [
				'quality' => $quality,
			]);
		}

		return $this;
	}

	public function fitIn($width = 0, $height = 0, $fill = 'transparent')
	{
		$this->_hasFilter = true;

		$this->_transformations = array_merge($this->_transformations, [
			'width' => $width,
			'height' => $height,
			'fill' => $fill,
			'fit-in' => true,
		]);

		// has to be an image that supports transparency
		if ($fill === 'transparent') {
			$this->format('png');
		}

		return $this;
	}

	/**
	 * Transforms the image using the Storyblok image service
	 * See: https://www.storyblok.com/docs/image-service
	 *
	 * @param $param
	 * @return string
	 */
	public function transform($param): string
	{
		$resource = str_replace(['https:', '//a.storyblok.com'], '', $this->content['filename']);
		return '//img2.storyblok.com' . $param . $resource;
	}
}