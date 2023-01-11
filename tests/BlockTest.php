<?php

namespace Riclep\Storyblok\Tests;


use Illuminate\Support\Collection;
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
	public function can_render_block()
	{
		$page = $this->makePage();
		$block = $page->block();

		config(['storyblok.view_path' => 'Fixtures.views.']);

		$this->assertEquals('text', (string) $block->render());
	}


	/** @test */
	public function can_render_using()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertEquals('rendering using text', (string) $block->renderUsing(['Fixtures.views.blocks.render-using']));
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
	public function can_load_single_relation_with_custom_class()
	{
		$page = $this->makePage('relation-custom.json');
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\CustomTwo', $block->single_option_story);
	}

	/** @test */
	public function can_load_multiple_relations()
	{
		$page = $this->makePage('relation-custom.json');
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\CustomTwo', $block->multi_options_stories[0]);
	}

	/** @test */
	public function can_load_multiple_relations_with_custom_class()
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
	public function can_get_find_child_block()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertTrue($block->hasChildBlock('blocks', 'Person'));
		$this->assertFalse($block->hasChildBlock('blocks', 'NotHere'));

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
	public function can_cast_block_to_string()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block();

		$this->assertEquals('{"text":"text","image":{"transformations":{"desktop":{"src":{},"media":"(min-width: 1200px)"},"mobile":{"src":{},"media":"(min-width: 600px)"}}},"datetime":{},"blocks":[{"columns":[{"Name":{},"Surname":"surname","Text":"This is some text to test typography is applied 10x10 let\'s check","Html":{}}]}]}', (string) $block);
	}

	/** @test */
	public function can_interate_over_fields()
	{
		$page = $this->makePage('custom-page2.json');
		$block = $page->block();

		foreach ($block as $key => $value) {
			$this->assertEquals('text', $key);
			$this->assertEquals('text', $value);
		}
	}

	/** @test */
	public function can_call_fields_ready()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block();

		$this->assertEquals('yes', $block->added);
	}

	/** @test */
	public function will_throw_exception_with_view_not_found()
	{
		$this->expectException(\Riclep\Storyblok\Exceptions\UnableToRenderException::class);
		$this->expectExceptionMessage('None of the views in the given array exist.');
		$page = $this->makePage('custom-page.json');

		$page->block()->renderUsing('i-do-not-exist');
	}

	/** @test */
	public function block_can_have_settings()
	{
		$page = $this->makePage('has-settings.json');
		$block = $page->block();


		$this->assertInstanceOf(Collection::class, $block->hasSettings());

		$page2 = $this->makePage('custom-page.json');
		$block2 = $page2->block();

		$this->assertNull($block2->hasSettings());
	}

	/** @test */
	public function block_can_have_setting()
	{
		$page = $this->makePage('has-settings.json');
		$block = $page->block();

		$this->assertInstanceOf(Collection::class, $block->hasSetting('lsf_linked_field'));
		$this->assertFalse($block->hasSetting('not_here'));
	}

	/** @test */
	public function block_can_get_setting()
	{
		$page = $this->makePage('has-settings.json');
		$block = $page->block();

		$this->assertInstanceOf(Collection::class, $block->settings());
	}

	/** @test */
	public function block_settings_can_process_comma_separated_list()
	{
		$page = $this->makePage('has-settings.json');
		$block = $page->block();

		$this->assertEquals(['quarter', 'half', 'full'], $block->settings('lsf_style')['commas']);
	}

	/** @test */
	public function will_return_mutli_self_fields()
	{
		$page = $this->makePage('multi-option-self.json');
		$block = $page->block();

		$this->assertEquals([
			0 => '20',
			1 => '10',
			2 => '30',
		], $block->body[0]->test_field);
	}
}
