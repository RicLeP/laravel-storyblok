<?php

namespace Riclep\Storyblok\Tests;


class PageTest extends TestCase
{
	public function test_can_get_default_page_class()
	{
		$page = $this->makePage();

		$this->assertInstanceOf('Riclep\Storyblok\Page', $page);
	}

	/** @xxxtest */
	public function can_get_bespoke_page_class()
	{
		// TODO
	}

	public function test_can_get_publish_date()
	{
		$page = $this->makePage();

		$this->assertEquals('2020-07-06 10:24:21', $page->publishedAt()->toDateTimeString());
	}

	public function test_can_get_updated_date()
	{
		$page = $this->makePage();

		$this->assertEquals('2020-08-06 10:24:21', $page->updatedAt()->toDateTimeString());
	}

	public function test_can_get_slug()
	{
		$page = $this->makePage();

		$this->assertEquals('all-fields', $page->slug());
	}

	public function test_get_tags()
	{
		$page = $this->makePage();

		$this->assertEquals(['tag2', 'tag1'], $page->tags());
	}

	public function test_get_tags_alphabetically()
	{
		$page = $this->makePage();

		$this->assertEquals(['tag1', 'tag2'], $page->tags(true));
	}

	public function test_has_tag()
	{
		$page = $this->makePage();

		$this->assertTrue($page->hasTag('tag1'));
		$this->assertFalse($page->hasTag('tag3'));
	}

	public function test_get_content_block()
	{
		$page = $this->makePage();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Block', $page->block());
	}

	public function test_can_get_bespoke_page_content_block_class()
	{
		$page = $this->makePage('custom-page.json');

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\Custom', $page->block());
	}

	public function test_can_get_content_from_page_block()
	{
		$page = $this->makePage();

		$this->assertEquals('text', $page->text);
	}

	public function test_can_add_schema_org()
	{
		$page = $this->makePage('custom-page.json');

		$this->assertInstanceOf('Spatie\SchemaOrg\LocalBusiness', $page->meta()['schema_org'][1]);
	}

	public function test_can_read_schema_org()
	{
		$page = $this->makePage('custom-page.json');

		$this->assertEquals('<script type="application/ld+json">{"@context":"https://schema.org","@type":"Person","givenName":"In the Person block","email":"ric@sirric.co.uk"}</script><script type="application/ld+json">{"@context":"https://schema.org","@type":"LocalBusiness","name":"On the page","email":"ric@sirric.co.uk","contactPoint":{"@type":"ContactPoint","areaServed":"Worldwide"}}</script>', $page->schemaOrgScript());
	}

	public function test_can_get_page_view()
	{
		$page = $this->makePage('custom-page.json');

		$this->assertEquals(['pages.custom', 'pages.page'], $page->views());
	}
}
