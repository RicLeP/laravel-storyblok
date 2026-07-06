<?php

namespace Riclep\Storyblok\Listeners;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\Events\PublishingEvent;

class ClearCache
{
    /**
     * Handle the event.
     */
    public function handle(PublishingEvent $event): void
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::store(config('storyblok.sb_cache_driver'))->tags('storyblok')->flush();
        } else {
            // Cache::flush();
            Cache::store(config('storyblok.sb_cache_driver'))->flush();
        }
    }
}
