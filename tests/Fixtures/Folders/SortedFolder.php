<?php

namespace Riclep\Storyblok\Tests\Fixtures\Folders;

use Riclep\Storyblok\Folder;

class SortedFolder extends Folder
{
    protected function setDefaults(): void
    {
        $this->desc('content.date')->perPage(17);
    }
}
