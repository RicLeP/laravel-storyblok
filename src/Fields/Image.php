<?php

namespace Riclep\Storyblok\Fields;

use Riclep\Storyblok\Support\ImageTransformation;

/**
 * @property bool $focus
 */
class Image extends Asset
{
    /**
     * Stores the transformations to be applied
     */
    public array $transformations = [];

    /**
     * The transformer used for this image
     */
    protected mixed $transformer;

    /**
     * The transformer class used for transformations
     */
    protected mixed $transformerClass;

    /**
     * Constructs the image image field
     */
    public function __construct($content, $block)
    {
        if (is_string($content)) {
            $this->upgradeStringFields($content);
            parent::__construct($this->content, $block);
        } else {
            parent::__construct($content, $block);
        }

        $this->transformerClass = config('storyblok.image_transformer');

        $transformerClass = $this->transformerClass;
        $this->transformer = new $transformerClass($this);

        if (method_exists($this->transformer, 'init')) {
            $this->transformer->init();
        }

        if (method_exists($this, 'transformations')) {
            $this->transformations();
        }
    }

    /**
     * Get the width of the image or transformed image
     */
    public function width(bool $original = false): int
    {
        return $this->transformer->width($original);
    }

    /**
     * Get the height of the image or transformed image
     */
    public function height(bool $original = false): int
    {
        return $this->transformer->height($original);
    }

    /**
     * Get the mime of the image or transformed image
     */
    public function mime(bool $original = false): string
    {
        return $this->transformer->mime($original);
    }

    /**
     * Get the alt text of the image. If none is set, return the default
     */
    public function alt(string $default = ''): string
    {
        return $this->content()['alt'] ?: $default;
    }

    /**
     * Get the copyright text of the image. If none is set, return the default
     */
    public function copyright(string $default = ''): string
    {
        return $this->content()['copyright'] ?: $default;
    }

    /**
     * Get the name (called description in the editor) of the image. If none is set, return the default
     */
    public function name(string $default = ''): string
    {
        return $this->content()['name'] ?: $default;
    }

    /**
     * Get the source of the image. If none is set, return the default
     */
    public function source(string $default = ''): string
    {
        return $this->content()['source'] ?: $default;
    }

    /**
     * Get the title of the image. If none is set, return the default
     */
    public function title(string $default = ''): string
    {
        return $this->content()['title'] ?: $default;
    }

    /**
     * Create a new or get a transformation of the image
     */
    public function transform($tranformation = null): mixed
    {
        if ($tranformation) {
            if (array_key_exists($tranformation, $this->transformations)) {
                return new ImageTransformation($this->transformations[$tranformation]);
            }

            return false;
        }

        $transformerClass = $this->transformerClass;
        $this->transformer = new $transformerClass($this);

        if (method_exists($this->transformer, 'init')) {
            $this->transformer->init();
        }

        return $this->transformer;
    }

    /**
     * Set the driver to use for transformations
     */
    public function transformer($transformer): mixed
    {
        $this->transformerClass = $transformer;

        return $this;
    }

    /**
     * Returns a picture element tag for this image and
     * ant transforms defined on the image class
     */
    public function picture(string $alt = '', $default = null, array $attributes = [], string $view = 'laravel-storyblok::picture-element', bool $reverseTagOrder = false): string
    {
        if ($default) {
            $imgSrc = (string) $this->transformations[$default]['src'];
        } else {
            $imgSrc = $this->filename;
        }

        // srcset seems to work the opposite way to picture elements when working out sizes
        if ($reverseTagOrder) {
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

    /**
     * Returns an image tag with srcset attribute
     */
    public function srcset(string $alt = '', $default = null, array $attributes = [], string $view = 'laravel-storyblok::srcset'): string
    {
        return $this->picture($alt, $default, $attributes, $view ?? 'laravel-storyblok::srcset');
    }

    /**
     * Allows setting of new transformations on this image. Optionally
     * return a new image so the original is not mutated
     */
    public function setTransformations($transformations, bool $mutate = true): static
    {
        if ($mutate) {
            $this->transformations = $transformations;

            return $this;
        }

        $class = get_class($this); // don’t mutate original object
        $image = new $class($this->content, $this->block);
        $image->transformations = $transformations;

        return $image;
    }

    /**
     * Reads the focus property if available and returns a string that can be used for CSS
     * object-position or background-position. The default should be any valid value for
     * the CSS property being used. Rigid will use hard alignments to edges.
     */
    public function focalPointAlignment($default = 'center', $rigid = false): string
    {
        if (! $this->focus) {
            return $default;
        }

        preg_match_all('/\d+/', $this->focus, $matches);

        $leftPercent = round(($matches[0][0] / $this->width(true)) * 100);
        $topPercent = round(($matches[0][1] / $this->height(true)) * 100);

        if ($rigid) {
            if ($leftPercent > 66) {
                $horizontalAlignment = 'right';
            } elseif ($leftPercent > 33) {
                $horizontalAlignment = 'center';
            } else {
                $horizontalAlignment = 'left';
            }

            if ($topPercent > 66) {
                $verticalAlignment = 'bottom';
            } elseif ($topPercent > 33) {
                $verticalAlignment = 'center';
            } else {
                $verticalAlignment = 'top';
            }

            return $horizontalAlignment.' '.$verticalAlignment;
        }

        return $leftPercent.'% '.$topPercent.'%';
    }

    /**
     * Returns if the image is portrait
     */
    public function isPortrait(): bool
    {
        return $this->width() < $this->height();
    }

    /**
     * Returns if the image is landscape
     */
    public function isLandscape(): bool
    {
        return $this->width() > $this->height();
    }

    /**
     * Returns if the image is square
     */
    public function isSquare(): bool
    {
        return $this->width() === $this->height();
    }

    /**
     * Returns the image orientation
     */
    public function orientation(): string
    {
        if ($this->isPortrait()) {
            return 'portrait';
        }

        if ($this->isLandscape()) {
            return 'landscape';
        }

        return 'square';
    }

    /**
     * Converts string fields into full image fields
     */
    protected function upgradeStringFields($content): void
    {
        $this->content = [
            'filename' => $content,
            'alt' => null,
            'copyright' => null,
            'fieldtype' => 'asset',
            'focus' => null,
            'name' => '',
            'title' => null,
        ];
    }
}
