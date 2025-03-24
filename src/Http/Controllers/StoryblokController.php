<?php

namespace Riclep\Storyblok\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\StoryblokFacade as Storyblok;


class StoryblokController
{
    /**
     * Loads a story rendering the content in the matched view.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     * @throws \Exception
     */
    public function show($slug = 'home'): \Illuminate\View\View
    {
        if ($this->isDenylisted($slug)) {
            throw new \Riclep\Storyblok\Exceptions\DenylistedUrlException($slug);
        }

        return Storyblok::read($slug)->render();
    }

    /**
     * Deletes the cached API responses
     */
    public function destroy(): void
    {
        if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
            Cache::store(config('storyblok.sb_cache_driver'))->tags('storyblok')->flush();
        } else {
            Cache::store(config('storyblok.sb_cache_driver'))->flush();
        }
    }

    /**
     * Check if the slug is blacklisted.
     *
     * @param string $slug
     * @return bool
     */
    protected function isDenylisted(string $slug): bool
    {
        foreach (config('storyblok.denylist', []) as $pattern) {
            if ($pattern === $slug) {
                return true;
            }

            if (strlen($pattern) > 1 && $pattern[0] === '/' && substr($pattern, -1) === '/') {
                if (preg_match($pattern, $slug)) {
                    return true;
                }
            }
        }

        return false;
    }
}
