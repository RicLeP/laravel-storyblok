<?php

namespace Riclep\Storyblok\Support\ImageTransformers;

use Illuminate\Support\Str;

class Storyblok extends BaseTransformer
{
	/**
	 * Resizes the image and sets the focal point
	 *
	 * @param int $width
	 * @param int $height
	 * @param string $focus
	 * @return $this
	 */
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

	/**
	 * Fits the image in the given width and height
	 *
	 * @param $width
	 * @param $height
	 * @param $fill
	 * @return $this
	 */
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
			$this->format('webp');
		}

		return $this;
	}

	/**
	 * Set the image format you want returned
	 *
	 * @param $format
	 * @param $quality
	 * @return $this
	 */
	public function format($format, $quality = null)
	{
		$this->transformations = array_merge($this->transformations, [
			'format' => $format,
			'mime' => $this->setMime($format),
		]);

		if ($quality) {
			$this->transformations = array_merge($this->transformations, [
				'quality' => $quality,
			]);
		}

		return $this;
	}


	/**
	 * Creates the Storyblok image service URL
	 *
	 * @return string
	 */
	public function buildUrl() {
		if ($this->transformations === 'svg') {
			return $this->image->content()['filename'];
		}

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
		if ($this->hasFilters()) {
			$transforms .= $this->applyFilters();
		}

		return $this->assetDomain($transforms);
	}

	/**
	 * Applies the filters to the image service URL
	 *
	 * @return string
	 */
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

		if (array_key_exists('focus', $this->transformations) && $this->transformations['focus'] === 'focal-point' && $this->image->content()['focus']) {
			$filters .= ':focal(' . $this->image->content()['focus'] . ')';
		}

		return $filters;
	}

	/**
	 * Extracts meta details from the image. With Storyblok we can get a
	 * few things from the URL
	 *
	 * @return void|null
	 */
	protected function extractMetaDetails()
	{
		$path = $this->image->content()['filename'];

		preg_match_all('/(?<width>\d+)x(?<height>\d+).+\.(?<extension>[a-z]{3,4})/mi', $path, $dimensions, PREG_SET_ORDER, 0);

		if (Str::endsWith(strtolower($this->image->content()['filename']), '.svg')) {
			$this->meta = [
				'height' => false,
				'width' => false,
				'extension' => 'svg',
				'mime' => 'image/svg+xml',
			];
		} else {
			$this->meta = [
				'height' => $dimensions[0]['height'],
				'width' => $dimensions[0]['width'],
				'extension' => strtolower($dimensions[0]['extension']),
				'mime' => $this->setMime(strtolower($dimensions[0]['extension'])),
			];
		}
	}

	/**
	 * Checks if any filters were applied to the transformation
	 *
	 * @return bool
	 */
	private function hasFilters() {
		return array_key_exists('format', $this->transformations) || array_key_exists('quality', $this->transformations) || array_key_exists('fill', $this->transformations) || (array_key_exists('focus', $this->transformations) && $this->transformations['focus'] === 'focal-point');
	}

	/**
	 * Sets the asset domain
	 *
	 * @param $options
	 * @return string
	 */
	protected function assetDomain($options = null): string
	{
		$resource = str_replace(config('storyblok.asset_domain'), config('storyblok.image_service_domain'), $this->image->content()['filename']);

		return $resource . '/m' . $options;
	}
}