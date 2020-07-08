<?php

namespace Riclep\Storyblok\Tests;

use Riclep\Storyblok\Page;

class PageTest extends TestCase
{
	/** @test */
	public function can_get_default_page_class()
	{
		$page = $this->makePage();
		$this->assertInstanceOf('Riclep\Storyblok\Page', $page);
	}

	/** @xxtest */
	public function can_get_bespoke_page_class()
	{
		// TODO
	}

	/** @test */
	public function can_get_publish_date()
	{
		$page = $this->makePage();

		$this->assertEquals('2020-07-06 10:24:21', $page->publishedAt()->toDateTimeString());
	}

	/** @test */
	public function can_get_updated_date()
	{
		$page = $this->makePage();

		$this->assertEquals('2020-08-06 10:24:21', $page->updatedAt()->toDateTimeString());
	}

	/** @test */
	public function can_get_slug()
	{
		$page = $this->makePage();

		$this->assertEquals('all-fields', $page->slug());
	}

	/** @test */
	public function get_tags()
	{
		$page = $this->makePage();

		$this->assertEquals(['tag2', 'tag1'], $page->tags());
	}

	/** @test */
	public function get_tags_alphabetically()
	{
		$page = $this->makePage();

		$this->assertEquals(['tag1', 'tag2'], $page->tags(true));
	}

	/** @test */
	public function has_tag()
	{
		$page = $this->makePage();

		$this->assertTrue($page->hasTag('tag1'));
		$this->assertFalse($page->hasTag('tag3'));
	}

	/** @test */
	public function get_content_block()
	{
		$page = $this->makePage();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Block', $page->block());
	}

	/** @test */
	public function can_get_bespoke_content_block_class()
	{
		$page = $this->makePage('custom-page.json');

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\Custom', $page->block());
	}

	/** @test */
	public function can_get_content_from_page_block()
	{
		$page = $this->makePage();

		$this->assertEquals('text', $page->text);
	}
}
