<?php

namespace Riclep\Storyblok\Tests;


use Illuminate\Support\Collection;
use Riclep\Storyblok\Page;
use Riclep\Storyblok\Tests\Fixtures\Folders\Folder;
use Riclep\Storyblok\Tests\Fixtures\Folders\EmptyFolder;
use Riclep\Storyblok\Tests\Fixtures\Folders\SortedFolder;

class FolderTest extends TestCase
{
    public function test_can_get_total_stories() {
		$folder = new Folder();
		$folder->read();

		$folder2 = new EmptyFolder();
		$folder2->read();

		$this->assertEquals(15, $folder->totalStories);
		$this->assertEquals(0, $folder2->totalStories);
	}

    public function test_can_get_total_stories_for_this_page() {
		$folder = new Folder();
		$folder->read();

		$this->assertEquals(4, $folder->count());
	}

    public function test_will_return_zero_stories_for_empty_folder() {
		$folder = new EmptyFolder();
		$folder->read();

		$this->assertEquals(0, $folder->count());
	}

    public function test_can_get_folder_stories() {
		$folder = new Folder();
		$folder->read();

		$this->assertInstanceOf(Collection::class, $folder->stories);
		$this->assertInstanceOf(Page::class, $folder->stories[0]);
	}

    public function test_can_paginate_folder() {
		$folder = new Folder();
		$folder->read();

		$this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $folder->paginate());
	}

    public function test_can_use_fluent_access() {
		$folder = new Folder();
		$folder->slug('services')->desc()->perPage(5);
		$request = $folder->makeRequest()->toArray();

		$this->assertEquals([
			'language' => 'default',
			'sort_by' => 'published_at:desc',
			'starts_with' => 'services',
			'page' => 1,
			'per_page' => 5,
		], $request);


		$folder2 = new Folder();
		$folder2->slug('services')->asc('content.name');

		$request2 = $folder2->makeRequest()->toArray();

		$this->assertEquals([
			'language' => 'default',
			'sort_by' => 'content.name:asc',
			'starts_with' => 'services',
			'page' => 1,
			'per_page' => 10,
		], $request2);
	}

    public function test_can_set_fluent_settings() {
		$folder = new Folder();
		$folder->isStartpage(false)
			->slug('services')
			->perPage(7);

		$request = $folder->makeRequest()->toArray();

		$this->assertEquals([
			'language' => 'default',
			'is_startpage' => 0,
			'starts_with' => 'services',
			'page' => 1,
			'per_page' => 7,
		], $request);
	}

    public function test_can_add_settings_in_folder_constructor() {
		$folder = new SortedFolder();
		$request = $folder->makeRequest()->toArray();

		$this->assertEquals([
			'language' => 'default',
			'sort_by' => 'content.date:desc',
			'page' => 1,
			'per_page' => 17,
		], $request);


		$folder2 = new SortedFolder();

		// calling perPage overrides the settings in the constructor
		$folder2->perPage(8);
		$request2 = $folder2->makeRequest()->toArray();

		$this->assertEquals([
			'language' => 'default',
			'sort_by' => 'content.date:desc',
			'page' => 1,
			'per_page' => 8,
		], $request2);
	}
}

