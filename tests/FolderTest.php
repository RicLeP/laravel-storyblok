<?php

namespace Riclep\Storyblok\Tests;



use Riclep\Storyblok\Tests\Fixtures\Folders\Folder;

class FolderTest extends TestCase
{

	/** @test */
	public function can_load_stories_from_folder()
	{
		$folder = new Folder();
		$folder->slug('episodes');
		dd($folder->read());
	}

}
