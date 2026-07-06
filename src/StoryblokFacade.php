<?php

namespace Riclep\Storyblok;

use Illuminate\Support\Facades\Facade;

class StoryblokFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'storyblok';
    }
}
