<?php


namespace Riclep\Storyblok\Tests\Fixtures\Folders;


use Illuminate\Support\Collection;

class SortedFolder extends \Riclep\Storyblok\Folder
{
	protected function setDefaults(): void
	{
		$this->desc('content.date')->perPage(17);
	}
}