<?php

namespace Riclep\Storyblok\Support\ImageDrivers;

use Illuminate\Support\Str;

class Img2Storyblok extends BaseDriver
{


	protected function hasFile() {
		return $this->image->hasFile();
	}


	public function resize($width = 0, $height = 0, $focus = null)
	{
		$this->image->transformations = array_merge($this->image->transformations, [
			'width' => $width,
			'height' => $height,
		]);

		if ($focus) {
			$this->image->transformations = array_merge($this->image->transformations, [
				'focus' => $focus,
			]);
		}

		return $this;
	}

	public function format($format, $quality = null)
	{
		$this->image->transformations = array_merge($this->image->transformations, [
			'format' => $format,
		]);

		if ($quality) {
			$this->image->transformations = array_merge($this->image->transformations, [
				'quality' => $quality,
			]);
		}

		return $this;
	}

	public function fitIn($width = 0, $height = 0, $fill = 'transparent')
	{
		$this->image->transformations = array_merge($this->image->transformations, [
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
	public function createUrl($options = null): string
	{
		$resource = str_replace(['https:', '//' . config('storyblok.asset_domain')], '', $this->image->content()['filename']);
		return '//' . config('storyblok.image_service_domain') . $options . $resource;
	}

	private function hasFilters() {
		return array_key_exists('format', $this->image->transformations) || array_key_exists('quality', $this->image->transformations) || array_key_exists('fill', $this->image->transformations) || (array_key_exists('focus', $this->image->transformations) && $this->image->transformations['focus'] === 'focal-point');
	}

	private function applyFilters() {
		$filters = '/filters';

		if (array_key_exists('format', $this->image->transformations)) {
			$filters .= ':format(' . $this->image->transformations['format'] . ')';
		}

		if (array_key_exists('quality', $this->image->transformations)) {
			$filters .= ':quality(' . $this->image->transformations['quality'] . ')';
		}

		if (array_key_exists('fill', $this->image->transformations)) {
			$filters .= ':fill(' . $this->image->transformations['fill'] . ')';
		}

		if (array_key_exists('focus', $this->image->transformations) && $this->image->transformations['focus'] === 'focal-point' && $this->image->focus) {
			$filters .= ':focal(' . $this->image->focus . ')';
		}

		return $filters;
	}



	public function __toString()
	{
		if (Str::endsWith($this->image->content()['filename'], 'svg')) {
			return $this->image->content()['filename'];
		}

		$transforms = '';

		if (array_key_exists('fit-in', $this->image->transformations)) {
			$transforms .= '/fit-in';
		}

		if (array_key_exists('width', $this->image->transformations)) {
			$transforms .= '/' . $this->image->transformations['width'] . 'x' . $this->image->transformations['height'];
		}

		if (array_key_exists('focus', $this->image->transformations) && $this->image->transformations['focus'] === 'smart') {
			$transforms .= '/smart';
		}

		// filters
		if ($this->hasFilters()) {
			$transforms .= $this->applyFilters();
		}

		return $this->createUrl($transforms);
	}





	protected function extractMetaDetails() {
		$path = $this->image->content()['filename'];

		preg_match_all('/(?<width>\d+)x(?<height>\d+).+\.(?<extension>[a-z]{3,4})/mi', $path, $dimensions, PREG_SET_ORDER, 0);

		if (Str::endsWith(strtolower($this->image->content()['filename']), '.svg')) {
			$this->image->addMeta([
				'height' => false,
				'width' => false,
				'extension' => 'svg',
			]);
		} else {
			$this->image->addMeta([
				'height' => $dimensions[0]['height'],
				'width' => $dimensions[0]['width'],
				'extension' => strtolower($dimensions[0]['extension']),
			]);
		}
	}
}