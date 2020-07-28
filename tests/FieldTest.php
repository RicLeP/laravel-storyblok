<?php

namespace Riclep\Storyblok\Tests;


use Riclep\Storyblok\Fields\Asset;
use Riclep\Storyblok\Fields\AssetLink;
use Riclep\Storyblok\Fields\DateTime;
use Riclep\Storyblok\Fields\EmailLink;
use Riclep\Storyblok\Fields\Image;
use Riclep\Storyblok\Fields\Markdown;
use Riclep\Storyblok\Fields\MultiAsset;
use Riclep\Storyblok\Fields\RichText;
use Riclep\Storyblok\Fields\StoryLink;
use Riclep\Storyblok\Fields\Textarea;
use Riclep\Storyblok\Fields\UrlLink;
use Riclep\Storyblok\Tests\Fixtures\AssetWithAccessor;

class FieldTest extends TestCase
{
	private function getFieldContents($field) {
		$story = json_decode(file_get_contents(__DIR__ . '/Fixtures/all-fields.json'), true);
		return $story['story']['content'][$field];
	}

	/** @test */
	public function can_read_text()
	{
		$field = $this->getFieldContents('text');
		$this->assertEquals('text', (string) $field);
	}

	/** @test */
	public function can_convert_text_area_to_html()
	{
		$field = new Textarea($this->getFieldContents('textarea'), null);
		$this->assertEquals('<p>textarea</p><p>textarea</p>', (string) $field);
	}

	/** @test */
	public function can_convert_markdown_to_html()
	{
		$field = new Markdown($this->getFieldContents('markdown'), null);
		$this->assertEquals("<p><strong>markdown</strong>\nmarkdown</p>\n", (string) $field);
	}

	/** @test */
	public function can_convert_rich_text_to_string()
	{
		$field = new RichText($this->getFieldContents('richtext'), null);
		$this->assertEquals('<p><b>textarea</b></p><p>richtext</p>', (string) $field);
	}

	/** @test */
	public function can_convert_date_to_carbon()
	{
		$field = new DateTime($this->getFieldContents('datetime'), null);
		$this->assertInstanceOf('Carbon\Carbon', $field->content());
	}

	/** @test */
	public function can_convert_date_to_string()
	{
		$field = new DateTime($this->getFieldContents('datetime'), null);
		$this->assertEquals('2020-07-01 20:57:00', (string) $field);
	}

	/** @test */
	public function can_get_asset_as_url()
	{
		$field = new Asset($this->getFieldContents('asset'), null);
		$this->assertEquals('https://a.storyblok.com/f/52681/700x700/97f51f6374/blood-cells.pdf', (string) $field);
	}

	/** @test */
	public function can_get_image_asset_as_url()
	{
		$field = new Image($this->getFieldContents('asset_image'), null);
		$this->assertEquals('https://a.storyblok.com/f/52681/700x700/97f51f6374/blood-cells.jpg', (string) $field);
	}

	/** @test */
	public function can_get_asset_url_with_accessor()
	{
		$field = new AssetWithAccessor($this->getFieldContents('asset'), null);
		$this->assertEquals('fdp.sllec-doolb/4736f15f79/007x007/18625/f/moc.kolbyrots.a//:sptth', $field->filename_backwards);
	}

	/** @test */
	public function can_check_asset_has_file()
	{
		$field = new Asset($this->getFieldContents('asset'), null);
		$this->assertTrue($field->hasFile());

		$field = new Asset($this->getFieldContents('asset_empty'), null);
		$this->assertFalse($field->hasFile());
	}

	/** @test */
	public function can_check_multi_asset_has_files()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block(); // any parent block will for for testing

		$field = new MultiAsset($this->getFieldContents('multi_assets'), $block);
		$this->assertTrue($field->hasFiles());

		$field = new MultiAsset($this->getFieldContents('multi_assets_empty'), $block);
		$this->assertFalse($field->hasFiles());
	}

	/** @test */
	public function can_use_array_access_on_multi_asset()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block(); // any parent block will for for testing

		$field = new MultiAsset($this->getFieldContents('multi_assets'), $block);
		$this->assertEquals('https://a.storyblok.com/f/52681/1000x875/7ced1a10b2/blow-dry-mobile.jpg', $field[0]->filename);
	}

	/** @test */
	public function can_make_assets_from_multi_asset()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block(); // any parent block will for for testing

		$field = new MultiAsset($this->getFieldContents('multi_assets'), $block);
		$this->assertInstanceOf('Riclep\Storyblok\Fields\Image', $field[0]);
		$this->assertInstanceOf('Riclep\Storyblok\Fields\Asset', $field[1]);
	}

	/** @test */
	public function can_get_email_link_address()
	{
		$field = new EmailLink($this->getFieldContents('link_email'), null);
		$this->assertEquals('ric@sirric.co.uk', (string) $field);
	}

	/** @test */
	public function can_get_asset_link_url()
	{
		$field = new AssetLink($this->getFieldContents('link_asset'), null);
		$this->assertEquals('https://a.storyblok.com/f/52681/700x700/97f51f6374/blood-cells.jpg', (string) $field);
	}

	/** @test */
	public function can_get_url_link_url()
	{
		$field = new UrlLink($this->getFieldContents('link_url'), null);
		$this->assertEquals('https://sirric.co.uk', (string) $field);
	}

	/** @test */
	public function can_get_story_link_url()
	{
		$field = new StoryLink($this->getFieldContents('link_story'), null);
		$this->assertEquals('key-people/primary-contact', (string) $field);
	}

	/** @test */
	public function can_use_custom_field_class()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Fields\Hero', $block->hero);
	}
}
