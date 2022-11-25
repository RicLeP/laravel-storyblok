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

	/** @test */
	public function can_use_fluent_access() {
		$folder = new Folder();
		$folder->slug('services')->desc()->perPage(5);
		$settings = $this::callMethod($folder, 'getSettings');

		$this->assertEquals([
			'is_startpage' => false,
			'sort_by' => 'published_at:desc',
			'starts_with' => 'services',
			'page' => 0,
			'per_page' => 5,
		], $settings);


		$folder2 = new Folder();
		$folder2->slug('services')->sort('content.name')->asc();

		$settings2 = $this::callMethod($folder2, 'getSettings');

		$this->assertEquals([
			'is_startpage' => false,
			'sort_by' => 'content.name:asc',
			'starts_with' => 'services',
			'page' => 0,
			'per_page' => 10,
		], $settings2);
	}

	/** @test */
	public function can_set_settings() {
		$folder = new Folder();
		$folder->settings([
			'is_startpage' => false,
			'starts_with' => 'services',
			'per_page' => 7,
		]);
		$settings = $this::callMethod($folder, 'getSettings');

		$this->assertEquals([
			'is_startpage' => false,
			'sort_by' => 'published_at:desc',
			'starts_with' => 'services',
			'page' => 0,
			'per_page' => 7,
		], $settings);
	}
}

