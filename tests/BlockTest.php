<?php

namespace Riclep\Storyblok\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Riclep\Storyblok\Page;

class BlockTest extends TestCase
{
	/** @test */
	public function can_extract_content()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertTrue($block->content()->has('text'));
		$this->assertFalse(array_key_exists('_editable', $block->content()));
	}

	/** @test */
	public function can_extract_meta()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertTrue(array_key_exists('_uid', $block->meta()));
		$this->assertTrue(array_key_exists('component', $block->meta()));
		$this->assertTrue(array_key_exists('_editable', $block->meta()));
	}

	/** @test */
	public function can_identify_rich_text_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\RichText', $block->richtext);
	}

	/** @test */
	public function can_identify_asset_link_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\AssetLink', $block->link_asset);
	}

	/** @test */
	public function can_identify_email_link_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\EmailLink', $block->link_email);
	}

	/** @test */
	public function can_identify_story_link_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\StoryLink', $block->link_story);
	}

	/** @test */
	public function can_identify_url_link_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\UrlLink', $block->link_url);
	}

	/** @test */
	public function can_identify_link_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\UrlLink', $block->link_url);
	}

	/** @test */
	public function can_identify_asset_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\Asset', $block->asset);
	}

	/** @test */
	public function can_identify_table_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\Table', $block->table);
	}

	/** @test */
	public function can_identify_blocks_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\Blocks', $block->blocks);
	}

	/** @test */
	public function can_read_text_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertEquals('text', $block->text);
	}

	/** @test */
	public function can_read_text_accessor_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertEquals('TEXT', $block->text_uppercase);
	}

	/** @test */
	public function can_read_richtext_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertEquals('<p><b>textarea</b></p><p>richtext</p>', $block->richtext);
	}
}
