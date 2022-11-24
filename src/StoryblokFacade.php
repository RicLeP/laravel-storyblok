<?php

namespace Riclep\Storyblok;

use Illuminate\Support\Facades\Facade;

class StoryblokFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'storyblok';
    }
}
