<?php

namespace Riclep\Storyblok\Tests\Fixtures\Folders;

use Illuminate\Support\Collection;
use Riclep\Storyblok\Folder;

class EmptyFolder extends Folder
{
    /**
     * Override the default folder as we don’t want to make an API
     * request within our test.
     *
     * @return Collection
     */
    protected function get()
    {
        $this->totalStories = 0;

        return collect(json_decode(file_get_contents(__DIR__.'/../empty-folder.json'), true)['stories']);
    }
}
