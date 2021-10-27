<?php

namespace Riclep\Storyblok\Tests;


use Riclep\Storyblok\RequestStory;

class BlockTest extends TestCase
{
	/** @test */
	public function can_extract_content()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertTrue($block->content()->has('text'));
		$this->assertFalse($block->content()->has('_editable'));
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
	public function can_get_uuid_from_meta()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertEquals('835f25ba-7c20-423c-b375-2690df006382', $block->uuid());
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
	public function can_identify_image_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\Image', $block->image);
	}

	/** @test */
	public function can_identify_table_field()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Fields\Table', $block->table);
	}

	/** @test */
	public function can_identify_custom_blocks_using_block_field_name()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Fields\PersonName', $block->blocks[0]->Name);
	}

	/** @test */
	public function can_identify_custom_blocks_using_block_name()
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
	public function can_load_relations()
	{
		$page = $this->makePage('relation.json');
		$block = $page->block();

		$blockMock = $this->createStub(RequestStory::class);
		$blockMock->method('get')->willReturn('');

		$relation = $this::callMethod($block, 'getRelation', [
			$blockMock,
			'all-fields'
		]);

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\AllFields', $relation);
	}

	/** @test */
	public function can_load_single_relation()
	{
		$page = $this->makePage('relation.json');
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\Custom', $block->single_option_story);
	}

	/** @test */
	public function can_load_multiple_relations()
	{
		$page = $this->makePage('relation.json');
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\Custom', $block->multi_options_stories[0]);
		$this->assertInstanceOf('Riclep\Storyblok\Block', $block->multi_options_stories[1]);
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

		$this->assertTrue($block->blocks[0]->isAncestorOf('page'));
		$this->assertFalse($block->blocks[0]->isAncestorOf('cats'));
	}

	/** @test */
	public function can_get_css_class()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertEquals('all-fields', $block->cssClass());
		$this->assertEquals('person', $block->blocks[0]->cssClass());
	}

	/** @test */
	public function can_get_css_class_with_parent()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertEquals('person@all-fields', $block->blocks[0]->cssClassWithParent());
	}

	/** @test */
	public function can_get_layout()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block();

		$this->assertEquals('layout_columns', $block->blocks[0]->columns[0]->getLayout());
	}

	/** @test */
	public function can_get_css_class_with_layout()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block();

		$this->assertEquals('person@layout_columns', $block->blocks[0]->columns[0]->cssClassWithLayout());
	}

	/** @test */
	public function nonexisting_field_returns_null_from_magic_getter()
	{
		$page = $this->makePage('custom-page.json');

		$this->assertNull($page->block()->not_here);
	}

	/** @test */
	public function can_add_schema_org()
	{
		$page = $this->makePage('custom-page.json');

		$this->assertInstanceOf('Spatie\SchemaOrg\Person', $page->meta()['schema_org'][0]);
	}

	/** @test */
	public function can_get_views()
	{
		$page = $this->makePage('ep16.json');

		$this->assertEquals(['blocks.layout_2-sections.text--titled', 'blocks.feature.text--titled', 'blocks.episode.text--titled', 'blocks.page.text--titled', 'blocks.text--titled'], $page->block()->features[0]->body[3]->section_1[0]->views());
	}

	/** @test */
	public function can_get_editorlink_in_edit_mode()
	{
		app()['config']->set('storyblok.edit_mode', true);
		$page = $this->makePage('ep16.json');

		$this->assertEquals('<!--#storyblok#{"name": "episode", "space": "87028", "uid": "f3a8d113-1d84-4bd5-acf4-09409e3852da", "id": "14005712"}-->', $page->block()->editorLink());
	}

	/** @test */
	public function editorlink_is_empty_when_not_in_edit_mode()
	{
		app()['config']->set('storyblok.edit_mode', false);
		$page = $this->makePage('ep16.json');

		$this->assertEquals('', $page->block()->editorLink());
	}

	/** @test */
	public function can_apply_typography()
	{
		$page = $this->makePage('custom-page.json');

		$this->assertEquals('This is some text to test typography is applied <span class="numbers">10</span>×<span class="numbers">10</span>&nbsp;let’s&nbsp;check', $page->blocks[0]->columns[0]->Text);

		$this->assertEquals('<p>This is some text to test typography is applied <span class="numbers">10</span>×<span class="numbers">10</span>&nbsp;let’s&nbsp;check</p><p>Another <span class="push-double"></span>​<span class="pull-double">“</span>paragraph”. <span class="numbers">3</span>×<span class="numbers">4</span>&nbsp;<span class="caps">CAPITALS</span>.</p>', $page->blocks[0]->columns[0]->Html);
	}

}
