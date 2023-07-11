<?php


namespace Riclep\Storyblok\Fields;

use Riclep\Storyblok\Support\ImageTransformers\StoryblokSvg;

/**
 * @property boolean $focus
 */
class SvgImage extends Image
{
    /**
	 * Create a new or get a transformation of the image
	 *
	 * @param $tranformation
	 * @return mixed
	 */
    public function transform($tranformation = null): mixed
    {
        return new StoryblokSvg($this);
    }
}
