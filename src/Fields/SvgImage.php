<?php

namespace Riclep\Storyblok\Fields;

use Riclep\Storyblok\Support\ImageTransformers\StoryblokSvg;

/**
 * @property bool $focus
 */
class SvgImage extends Image
{
    /**
     * Create a new or get a transformation of the image
     */
    public function transform($tranformation = null): mixed
    {
        return new StoryblokSvg($this);
    }
}
