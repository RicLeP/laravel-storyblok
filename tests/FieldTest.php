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
		$field = new Textarea($this->getFieldContents('textarea'));
		$this->assertEquals('<p>textarea</p><p>textarea</p>', (string) $field);
	}

	/** @test */
	public function can_convert_markdown_to_html()
	{
		$field = new Markdown($this->getFieldContents('markdown'));
		$this->assertEquals("<p><strong>markdown</strong>\nmarkdown</p>\n", (string) $field);
	}

	/** @test */
	public function can_convert_rich_text_to_string()
	{
		$field = new RichText($this->getFieldContents('richtext'));
		$this->assertEquals('<p><b>textarea</b></p><p>richtext</p>', (string) $field);
	}

	/** @test */
	public function can_convert_date_to_carbon()
	{
		$field = new DateTime($this->getFieldContents('datetime'));
		$this->assertInstanceOf('Carbon\Carbon', $field->content());
	}

	/** @test */
	public function can_convert_date_to_string()
	{
		$field = new DateTime($this->getFieldContents('datetime'));
		$this->assertEquals('2020-07-01 20:57:00', (string) $field);
	}

	/** @test */
	public function can_get_asset_as_url()
	{
		$field = new Asset($this->getFieldContents('asset'));
		$this->assertEquals('https://a.storyblok.com/f/52681/700x700/97f51f6374/blood-cells.jpg', (string) $field);
	}

	/** @test */
	public function can_get_image_asset_as_url()
	{
		$field = new Image($this->getFieldContents('asset_image'));
		$this->assertEquals('https://a.storyblok.com/f/52681/700x700/97f51f6374/blood-cells.jpg', (string) $field);
	}

	/** @test */
	public function can_get_asset_url_with_accessor()
	{
		$field = new AssetWithAccessor($this->getFieldContents('asset'));
		$this->assertEquals('gpj.sllec-doolb/4736f15f79/007x007/18625/f/moc.kolbyrots.a//:sptth', $field->filename_backwards);
	}

	/** @test */
	public function can_check_asset_has_file()
	{
		$field = new Asset($this->getFieldContents('asset'));
		$this->assertTrue($field->hasFile());

		$field = new Asset($this->getFieldContents('asset_empty'));
		$this->assertFalse($field->hasFile());
	}

	/** @test */
	public function can_check_multi_asset_has_files()
	{
		$field = new MultiAsset($this->getFieldContents('multi_assets'));
		$this->assertTrue($field->hasFiles());

		$field = new MultiAsset($this->getFieldContents('multi_assets_empty'));
		$this->assertFalse($field->hasFiles());
	}

	/** @test */
	public function can_use_array_access_on_multi_asset()
	{
		$field = new MultiAsset($this->getFieldContents('multi_assets'));
		$this->assertEquals('https://a.storyblok.com/f/52681/1000x875/7ced1a10b2/blow-dry-mobile.jpg', $field[0]->filename);
	}

	/** @test */
	public function can_make_assets_from_multi_asset()
	{
		$field = new MultiAsset($this->getFieldContents('multi_assets'));
		$this->assertInstanceOf('Riclep\Storyblok\Fields\Image', $field[0]);
		$this->assertInstanceOf('Riclep\Storyblok\Fields\Asset', $field[1]);
	}

	/** @test */
	public function can_get_email_link_address()
	{
		$field = new EmailLink($this->getFieldContents('link_email'));
		$this->assertEquals('ric@sirric.co.uk', (string) $field);
	}

	/** @test */
	public function can_get_asset_link_url()
	{
		$field = new AssetLink($this->getFieldContents('link_asset'));
		$this->assertEquals('https://a.storyblok.com/f/52681/700x700/97f51f6374/blood-cells.jpg', (string) $field);
	}

	/** @test */
	public function can_get_url_link_url()
	{
		$field = new UrlLink($this->getFieldContents('link_url'));
		$this->assertEquals('https://sirric.co.uk', (string) $field);
	}

	/** @test */
	public function can_get_story_link_url()
	{
		$field = new StoryLink($this->getFieldContents('link_story'));
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
