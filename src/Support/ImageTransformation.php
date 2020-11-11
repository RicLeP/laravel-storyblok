<?php


namespace Riclep\Storyblok\Support;


use Riclep\Storyblok\Fields\Image;

class ImageTransformation
{
	private $filename;
	private $focus;
	protected $transformations = [];
	private $image;

	public function __construct(Image $image)
	{
		$this->filename = $image->filename;
		$this->focus = $image->focus;
		$this->image = $image;
	}

	public function width() {
		return $this->transformations['width'] ?? $this->image->width();
	}

	public function height() {
		return $this->transformations['height'] ?? $this->image->height();
	}

	public function type() {
		if (array_key_exists('format', $this->transformations)) {
			return 'image/' . $this->transformations['format'];
		}

		return $this->image->type();
	}

	public function resize($width = 0, $height = 0, $focus = null)
	{
		$this->transformations = array_merge($this->transformations, [
			'width' => $width,
			'height' => $height,
		]);

		if ($focus) {
			$this->transformations = array_merge($this->transformations, [
				'focus' => $focus,
			]);
		}

		return $this;
	}

	public function format($format, $quality = null)
	{
		$this->transformations = array_merge($this->transformations, [
			'format' => $format,
		]);

		if ($quality) {
			$this->transformations = array_merge($this->transformations, [
				'quality' => $quality,
			]);
		}

		return $this;
	}

	public function fitIn($width = 0, $height = 0, $fill = 'transparent')
	{
		$this->transformations = array_merge($this->transformations, [
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
	 * @param $options
	 * @return string
	 */
	public function createUrl($options): string
	{
		$resource = str_replace(['https:', '//a.storyblok.com'], '', $this->filename);
		return '//img2.storyblok.com' . $options . $resource;
	}

	public function getTransformations() {
		return $this->transformations;
	}

	private function hasFilters() {
		return array_key_exists('format', $this->transformations) || array_key_exists('quality', $this->transformations) || array_key_exists('fill', $this->transformations) || (array_key_exists('focus', $this->transformations) && $this->transformations['focus'] === 'focal-point');
	}

	private function applyFilters() {
		$filters = '/filters';

		if (array_key_exists('format', $this->transformations)) {
			$filters .= ':format(' . $this->transformations['format'] . ')';
		}

		if (array_key_exists('quality', $this->transformations)) {
			$filters .= ':quality(' . $this->transformations['quality'] . ')';
		}

		if (array_key_exists('fill', $this->transformations)) {
			$filters .= ':fill(' . $this->transformations['fill'] . ')';
		}

		if (array_key_exists('focus', $this->transformations) && $this->transformations['focus'] === 'focal-point' && $this->focus) {
			$filters .= ':focal(' . $this->focus . ')';
		}

		return $filters;
	}

	public function __toString()
	{
		$transforms = '';

		if (array_key_exists('fit-in', $this->transformations)) {
			$transforms .= '/fit-in';
		}

		if (array_key_exists('width', $this->transformations)) {
			$transforms .= '/' . $this->transformations['width'] . 'x' . $this->transformations['height'];
		}

		if (array_key_exists('focus', $this->transformations) && $this->transformations['focus'] === 'smart') {
			$transforms .= '/smart';
		}

		// filters
		if ($this->hasFilters()) {
			$transforms .= $this->applyFilters();
		}

		return $this->createUrl($transforms);
	}
}