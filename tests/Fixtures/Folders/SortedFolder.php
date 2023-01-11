<?php


namespace Riclep\Storyblok\Tests\Fixtures\Folders;


use Illuminate\Support\Collection;

class SortedFolder extends \Riclep\Storyblok\Folder
{
	public function __construct()
	{
		// these are lost when $folder->settings() is called
		$this->settings([
			'sort_by' => 'content.date:desc',
			'per_page' => 17
		]);
	}
}