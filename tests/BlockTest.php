<?php

namespace Riclep\Storyblok\Tests;



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
	public function can_identify_create_blocks()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\Person', $block->blocks[0]);
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

	/** @test */
	public function can_cast_field_types()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Fields\HeroImage', $block->image);
		$this->assertInstanceOf('Riclep\Storyblok\Fields\DateTime', $block->datetime);
	}

	/** @test */
	public function can_get_parent()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Page', $block->parent());
		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Block', $block->blocks[0]->parent());
	}

	/** @test */
	public function can_get_page()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Page', $block->blocks[0]->page());

	}

	/** @test */
	public function can_get_component_path()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertEquals(['page', 'all-fields', 'person'], $block->blocks[0]->_componentPath);
	}

	/** @test */
	public function can_get_ancestor_component()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertEquals('all-fields', $block->blocks[0]->ancestorComponentName(1));
		$this->assertEquals('page', $block->blocks[0]->ancestorComponentName(2));
	}

	/** @test */
	public function can_check_is_child_of_component()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertTrue($block->blocks[0]->isChildOf('all-fields'));
		$this->assertFalse($block->blocks[0]->isChildOf('page'));
	}

	/** @test */
	public function can_check_is_ancestor_of_component()
	{
		$page = $this->makePage();
		$block = $page->block();

		dd('dd');

		$this->assertTrue($block->blocks[0]->isAncestorOf('page'));
		$this->assertFalse($block->blocks[0]->isAncestorOf('cats'));
	}

}
