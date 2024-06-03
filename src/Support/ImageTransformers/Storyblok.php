<?php

namespace Riclep\Storyblok\Support\ImageTransformers;

use Illuminate\Support\Str;

class Storyblok extends BaseTransformer
{

    /**
     * Performs any actions needed once the object is created
     * and any preprocessing is completed
     *
     * @return $this
     */
    public function init(): self
    {
        $this->extractMetaDetails();

        return $this;
    }

    /**
     * Resizes the image and sets the focal point
     *
     * @param int $width
     * @param int $height
     * @param string|null $focus
     * @return $this
     */
    public function resize(int $width = 0, int $height = 0, string $focus = null): self
    {
        $this->transformations = array_merge($this->transformations, [
            'width' => $width,
            'height' => $height,
        ]);

        if ($focus) {
            if ($focus === 'auto') {
                if ($this->image->focus) {
                    $focus = 'focal-point';
                } else {
                    $focus = 'smart';
                }
            }

            $this->transformations = array_merge($this->transformations, [
                'focus' => $focus,
            ]);
        }

        return $this;
    }

    /**
     * Fits the image in the given width and height
     *
     * @param int $width
     * @param int $height
     * @param string $fill
     * @return $this
     */
    public function fitIn(int $width = 0, int $height = 0, string $fill = 'transparent'): self
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
     * @param string $format
     * @param int|null $quality
     * @return $this
     */
    public function format(string $format, int $quality = null): self
    {
        $this->transformations = array_merge($this->transformations, [
            'format' => $format,
            'mime' => $this->setMime($format),
        ]);

        if ($quality !== null) {
            $this->transformations = array_merge($this->transformations, [
                'quality' => $quality,
            ]);
        }

        return $this;
    }

    /**
     * Make the image greyscale
     *
     * @return $this
     */
    public function grayscale(): self
    {
        $this->transformations = array_merge($this->transformations, [
            'grayscale' => true,
        ]);

        return $this;
    }

    /**
     * Set the image blur amount
     *
     * @param int $amount
     * @return $this
     */
    public function blur(int $amount): self
    {
        $this->transformations = array_merge($this->transformations, [
            'blur' => $amount,
        ]);

        return $this;
    }

    /**
     * Set the image brightness, use negative values to darken
     *
     * @param int $amount
     * @return $this
     */
    public function brightness(int $amount): self
    {
        $this->transformations = array_merge($this->transformations, [
            'brightness' => $amount,
        ]);

        return $this;
    }

    /**
     * Rotate the image, the allowed values are 90, 180 and 270
     *
     * @param int $amount
     * @return $this
     * @throws \Exception
     */
    public function rotate(int $amount): self
    {
        $allowedRotations = [90, 180, 270];

        if (!in_array($amount, $allowedRotations)) {
            throw new \Exception('Invalid rotation amount. Must be 90, 180 or 270');
        }

        $this->transformations = array_merge($this->transformations, [
            'rotate' => $amount,
        ]);

        return $this;
    }

    /**
     * Creates the Storyblok image service URL
     *
     * @return string
     */
    public function buildUrl(): string
    {
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
     * Checks if any filters were applied to the transformation
     *
     * @return bool
     */
    protected function hasFilters(): bool
    {
        $keys = ['format', 'quality', 'fill', 'focus', 'blur', 'brightness', 'rotate', 'grayscale'];

        foreach ($keys as $key) {
            if (array_key_exists($key, $this->transformations)) {
                if ($key === 'focus' && $this->transformations['focus'] !== 'focal-point') {
                    continue;
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Applies the filters to the image service URL
     *
     * @return string
     */
    protected function applyFilters(): string
    {
        $filters = '';

        if (array_key_exists('filters', $this->transformations)) {
            dd('ff');
        }

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

        if (array_key_exists('blur', $this->transformations)) {
            $filters .= ':blur(' . $this->transformations['blur'] . ')';
        }

        if (array_key_exists('brightness', $this->transformations)) {
            $filters .= ':brightness(' . $this->transformations['brightness'] . ')';
        }

        if (array_key_exists('rotate', $this->transformations)) {
            $filters .= ':rotate(' . $this->transformations['rotate'] . ')';
        }

        if (array_key_exists('grayscale', $this->transformations)) {
            $filters .= ':grayscale()';
        }

        if ($filters) {
            $filters = '/filters' . $filters;
        }

        return $filters;
    }

    /**
     * Extracts meta details from the image. With Storyblok we can get a
     * few things from the URL
     *
     * @return void
     */
    protected function extractMetaDetails(): void
    {
        $path = $this->image->content()['filename'];

        preg_match_all('/(?<width>\d+)x(?<height>\d+).+\.(?<extension>[a-z]{3,4})/mi', $path, $dimensions, PREG_SET_ORDER, 0);

        if ($dimensions) {
            if (Str::endsWith(strtolower($this->image->content()['filename']), '.svg')) {
                $this->meta = [
                    'height' => $dimensions[0]['height'],
                    'width' => $dimensions[0]['width'],
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
