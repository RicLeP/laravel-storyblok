<?php

namespace Riclep\Storyblok\Support\ImageDrivers;

use Illuminate\Support\Str;

class Storyblok extends BaseDriver
{



	protected function hasFile() {
		return $this->image->hasFile();
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

		$this->addMeta([
			'width' => $width,
			'height' => $height,
		], true);

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

		$this->addMeta([
			'extension' => $format,
			'mime' => $this->setMime($format),
		], true);

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

		$this->addMeta([
			'width' => $width,
			'height' => $height,
		], true);

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

		if (array_key_exists('focus', $this->transformations) && $this->transformations['focus'] === 'focal-point' && $this->image->focus) {
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

		dump($this->transformations);

		return $this->createUrl($transforms);
	}


	protected function extractMetaDetails() {
		$path = $this->image->content()['filename'];

		preg_match_all('/(?<width>\d+)x(?<height>\d+).+\.(?<extension>[a-z]{3,4})/mi', $path, $dimensions, PREG_SET_ORDER, 0);

		if (Str::endsWith(strtolower($this->image->content()['filename']), '.svg')) {
			$this->addMeta([
				'height' => false,
				'width' => false,
				'extension' => 'svg',
				'mime' => 'image/svg+xml',
			], true);
		} else {
			$this->addMeta([
				'height' => $dimensions[0]['height'],
				'width' => $dimensions[0]['width'],
				'extension' => strtolower($dimensions[0]['extension']),
				'mime' => $this->setMime(strtolower($dimensions[0]['extension'])),
			], true);
		}
	}
}