<?php

namespace Riclep\Storyblok\Tests\Fixtures\Folders;

use Illuminate\Support\Collection;

class Folder extends \Riclep\Storyblok\Folder
{
    /**
     * Override the default folder as we don’t want to make an API
     * request within our test.
     *
     * @return Collection
     */
    protected function get()
    {
        $this->totalStories = 15;

        return collect(json_decode(file_get_contents(__DIR__.'/../folder.json'), true)['stories']);
    }
}
