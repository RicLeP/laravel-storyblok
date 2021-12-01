<?php

namespace Riclep\Storyblok\Support\ImageTransformers;

use Illuminate\Support\Str;
use Imgix\UrlBuilder;

class Imgix extends BaseTransformer
{

	/**
	 * Resize the image to the given dimensions
	 *
	 * @param $width
	 * @param $height
	 * @return $this
	 */
	public function resize($width = 0, $height = 0)
	{
		$this->transformations = array_merge($this->transformations, [
			'w' => $width,
			'h' => $height,
		]);

		//dd($this);

		return $this;
	}

	/**
	 * Fit the image in the given dimensions
	 *
	 * @param $mode
	 * @param $options
	 * @return $this
	 */
	public function fit($mode, $options = [])
	{
		$this->transformations = array_merge($this->transformations, [
			'fit' => $mode
		]);

		$this->transformations = array_merge($this->transformations, $options);

		return $this;
	}

	/**
	 * Specify the crop type to use for the image
	 *
	 * @param $mode
	 * @param $options
	 * @return $this
	 */
	public function crop($mode, $options = [])
	{
		$this->transformations = array_merge($this->transformations, [
			'crop' => $mode
		]);

		$this->transformations = array_merge($this->transformations, $options);

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
		if ($format === 'auto') {
			$this->transformations = array_merge($this->transformations, [
				'auto' => 'format',
			]);
		} else {
			$this->transformations = array_merge($this->transformations, [
				'fm' => $format,
			]);

			if ($quality) {
				$this->transformations = array_merge($this->transformations, [
					'q' => $quality,
				]);
			}
		}

		return $this;
	}

	/**
	 * Manually set any options you want for the transformation as
	 * and array of key value pairs
	 *
	 * @param $options
	 * @return $this
	 */
	public function options($options)
	{
		$this->transformations = array_merge($this->transformations, $options);

		return $this;
	}


	/**
	 * Returns an imgix URL using their builder
	 *
	 * @return string
	 */
	public function buildUrl() {
		if ($this->transformations === 'svg') {
			return $this->image->content()['filename'];
		}

		$builder = new UrlBuilder(config('storyblok.imgix_domain'));
		$builder->setUseHttps(true);
		$builder->setSignKey(config('storyblok.imgix_token'));

		return $builder->createURL($this->image->content()['filename'], $this->transformations);
	}

	/**
	 * Gets the image meta from the given Storyblok URL
	 *
	 * @return void|null
	 */
	protected function extractMetaDetails() {
		$path = $this->image->content()['filename'];

		preg_match_all('/(?<width>\d+)x(?<height>\d+).+\.(?<extension>[a-z]{3,4})/mi', $path, $dimensions, PREG_SET_ORDER, 0);

		if ($dimensions) {
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
	}
}