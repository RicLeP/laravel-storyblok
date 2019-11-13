<?php

namespace Riclep\Storyblok;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Riclep\Storyblok\Skeleton\SkeletonClass
 */
class StoryblokFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'storyblok';
    }
}
