<?php

namespace Riclep\Storyblok\Support\ImageTransformers;

use Riclep\Storyblok\Fields\Image;

abstract class BaseTransformer
{
	/**
	 * Stores basic details about the image
	 *
	 * @var
	 */
	protected array $meta = [
		'height' => null,
		'width' => null,
		'extension' => null,
		'mime' => null,
	];

	/**
	 * Stores all the transformations
	 *
	 * @var array
	 */
	protected array $transformations = [];

	/**
	 * @return string The transformed image URL
	 */
	abstract public function buildUrl(): string;


	/**
	 * Extracts meta details for the current image such as width
	 * and height, mime and anything else of use
	 *
	 * @return void
	 */
	abstract protected function extractMetaDetails(): void;


	/**
	 * @param Image $image
	 */
	public function __construct(protected Image $image) {

		if (method_exists('preprocess', $this)) {
			$this->preprocess();
		}
	}

	/**
	 * Returns the width of the transformed image. Optionally you
	 * can request the original width
	 *
	 * @param bool $original
	 * @return int
	 */
	public function width(bool $original = false): int
	{
		if ($original) {
			return (int) $this->meta['width'];
		}

		// the width was not set so we need to compute it
		if (array_key_exists('width', $this->transformations) && $this->transformations['width'] === 0) {
			$scalePercent = round(($this->height() / $this->height(true) * 100), 2);

			return round((int) $this->meta['width'] / 100 * $scalePercent);
		}

		return $this->transformations['width'] ?? (int) $this->meta['width'];
	}

	/**
	 * Returns the height of the transformed image. Optionally you
	 * can request the original height
	 *
	 * @param bool $original
	 * @return int
	 */
	public function height(bool $original = false): ?int
	{
		if ($original) {
			return (int) $this->meta['height'];
		}

		// the height was not set so we need to compute it
		if (array_key_exists('height', $this->transformations) && $this->transformations['height'] === 0) {
			$scalePercent = round(($this->width() / $this->width(true) * 100), 2);

			return round((int) $this->meta['height'] / 100 * $scalePercent);
		}


		return $this->transformations['height'] ?? $this->meta['height'];
	}

	/**
	 * Returns the mime of the transformed image. Optionally you
	 * can request the original mime
	 *
	 * @param bool $original
	 * @return string
	 */
	public function mime(bool $original = false): ?string
	{
		if ($original) {
			return $this->meta['mime'];
		}

		return $this->transformations['mime'] ?? $this->meta['mime'];
	}

	/**
	 * Returns the mime from a particular file extension
	 *
	 * @param $extension
	 * @return string
	 */
	protected function setMime($extension): string
	{
		return $extension === 'jpg' ? 'image/jpeg' : 'image/' . $extension;
	}

	/**
	 * Casts the image transformation as a sting using the
	 * buildUrl method
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->buildUrl();
	}
}