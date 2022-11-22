<?php

namespace Riclep\Storyblok\Tests;


use Illuminate\Support\Collection;
use Riclep\Storyblok\Page;
use Riclep\Storyblok\Tests\Fixtures\Folders\Folder;
use Riclep\Storyblok\Tests\Fixtures\Folders\EmptyFolder;

class FolderTest extends TestCase
{
	/** @test */
	public function can_get_total_stories() {
		$folder = new Folder();
		$folder->read();

		$folder2 = new EmptyFolder();
		$folder2->read();

		$this->assertEquals(15, $folder->totalStories);
		$this->assertEquals(0, $folder2->totalStories);
	}

	/** @test */
	public function can_get_total_stories_for_this_page() {
		$folder = new Folder();
		$folder->read();

		$this->assertEquals(4, $folder->count());
	}

	/** @test */
	public function will_return_zero_stories_for_empty_folder() {
		$folder = new EmptyFolder();
		$folder->read();

		$this->assertEquals(0, $folder->count());
	}

	/** @test */
	public function can_get_folder_stories() {
		$folder = new Folder();
		$folder->read();

		$this->assertInstanceOf(Collection::class, $folder->stories);
		$this->assertInstanceOf(Page::class, $folder->stories[0]);
	}

	/** @test */
	public function can_paginate_folder() {
		$folder = new Folder();
		$folder->read();

		$this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $folder->paginate());
	}
}

