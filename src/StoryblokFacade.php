<?php

namespace Riclep\Storyblok;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bySlug(string $slug)
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
