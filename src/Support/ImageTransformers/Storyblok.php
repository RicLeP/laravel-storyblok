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
     * @return $this
     */
    public function resize(int $width = 0, int $height = 0, ?string $focus = null): self
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

        // fit-in and a manual crop are mutually exclusive - drop any crop
        // left over from a prior zoomCrop() call
        unset($this->transformations['crop']);

        return $this;
    }

    /**
     * Set the image format you want returned
     *
     * @return $this
     */
    public function format(string $format, ?int $quality = null): self
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
     * @return $this
     *
     * @throws \Exception
     */
    public function rotate(int $amount): self
    {
        $allowedRotations = [90, 180, 270];

        if (! in_array($amount, $allowedRotations)) {
            throw new \Exception('Invalid rotation amount. Must be 90, 180 or 270');
        }

        $this->transformations = array_merge($this->transformations, [
            'rotate' => $amount,
        ]);

        return $this;
    }

    /**
     * Works out a crop box around the focal point (or image centre) at the
     * given zoom level, storing it for buildUrl() to render. Chainable.
     *
     * @param  int  $zoom  Percentage, 100 = fills the frame with no extra
     *                     magnification. Values below 100 are clamped to 100.
     * @return $this
     */
    public function zoomCrop(int $width, int $height, int $zoom = 100): self
    {
        $zoom = max($zoom, 100);

        $imageWidth = $this->width();
        $imageHeight = $this->height();
        $targetRatio = $width / $height;

        if ($imageWidth / $imageHeight > $targetRatio) {
            $baseCropHeight = $imageHeight;
            $baseCropWidth = $imageHeight * $targetRatio;
        } else {
            $baseCropWidth = $imageWidth;
            $baseCropHeight = $imageWidth / $targetRatio;
        }

        $cropWidth = min($baseCropWidth * (100 / $zoom), $imageWidth);
        $cropHeight = min($baseCropHeight * (100 / $zoom), $imageHeight);

        if ($this->image->focus) {
            $focalPointCoords = explode('x', explode(':', $this->image->focus)[0]);
            $focalPoint = [(float) $focalPointCoords[0], (float) $focalPointCoords[1]];
        } else {
            $focalPoint = [$imageWidth / 2, $imageHeight / 2];
        }

        $cropLeft = $focalPoint[0] - $cropWidth / 2;
        $cropTop = $focalPoint[1] - $cropHeight / 2;

        if ($cropLeft < 0) {
            $cropLeft = 0;
        } elseif ($cropLeft + $cropWidth > $imageWidth) {
            $cropLeft = $imageWidth - $cropWidth;
        }

        if ($cropTop < 0) {
            $cropTop = 0;
        } elseif ($cropTop + $cropHeight > $imageHeight) {
            $cropTop = $imageHeight - $cropHeight;
        }

        $this->transformations = array_merge($this->transformations, [
            'width' => $width,
            'height' => $height,
            'crop' => [
                'left' => (int) round($cropLeft),
                'top' => (int) round($cropTop),
                'right' => (int) round($cropLeft + $cropWidth),
                'bottom' => (int) round($cropTop + $cropHeight),
            ],
        ]);

        // a manual crop and Storyblok's own focal filter both position the
        // image within the frame - stacking them would apply the shift twice,
        // so drop any focus transformation a prior resize() call may have set
        unset($this->transformations['focus']);

        return $this;
    }

    /**
     * Creates the Storyblok image service URL
     */
    public function buildUrl(): string
    {
        if ($this->transformations === 'svg') {
            return $this->assetDomain($this->image->content()['filename']);
        }

        $transforms = '';

        if (array_key_exists('crop', $this->transformations)) {
            $crop = $this->transformations['crop'];
            $transforms .= '/'.$crop['left'].'x'.$crop['top'].':'.$crop['right'].'x'.$crop['bottom'];
        } elseif (array_key_exists('fit-in', $this->transformations)) {
            $transforms .= '/fit-in';
        }

        if (array_key_exists('width', $this->transformations)) {
            $transforms .= '/'.$this->transformations['width'].'x'.$this->transformations['height'];
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
     */
    protected function applyFilters(): string
    {
        $filters = '';

        // A raw filter string
        if (array_key_exists('filters', $this->transformations)) {
            $filters .= ':'.$this->transformations['filters'];
        }

        if (array_key_exists('format', $this->transformations)) {
            $filters .= ':format('.$this->transformations['format'].')';
        }

        if (array_key_exists('quality', $this->transformations)) {
            $filters .= ':quality('.$this->transformations['quality'].')';
        }

        if (array_key_exists('fill', $this->transformations)) {
            $filters .= ':fill('.$this->transformations['fill'].')';
        }

        if (array_key_exists('focus', $this->transformations) && $this->transformations['focus'] === 'focal-point' && $this->image->content()['focus']) {
            $filters .= ':focal('.$this->image->content()['focus'].')';
        }

        if (array_key_exists('blur', $this->transformations)) {
            $filters .= ':blur('.$this->transformations['blur'].')';
        }

        if (array_key_exists('brightness', $this->transformations)) {
            $filters .= ':brightness('.$this->transformations['brightness'].')';
        }

        if (array_key_exists('rotate', $this->transformations)) {
            $filters .= ':rotate('.$this->transformations['rotate'].')';
        }

        if (array_key_exists('grayscale', $this->transformations)) {
            $filters .= ':grayscale()';
        }

        if ($filters) {
            $filters = '/filters'.$filters;
        }

        return $filters;
    }

    /**
     * Extracts meta details from the image. With Storyblok we can get a
     * few things from the URL
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
     */
    protected function assetDomain($options = null): string
    {
        $resource = str_replace(config('storyblok.asset_domain'), config('storyblok.image_service_domain'), $this->image->content()['filename']);

        if ($options) {
            return $resource.'/m'.$options;
        }

        return $resource;
    }
}
