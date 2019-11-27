<?php

namespace Riclep\Storyblok\Tests\Feature;


use Riclep\Storyblok\Storyblok;
use Tests\TestCase;

class PageTest extends \Riclep\Storyblok\Tests\TestCase
{
	/** @test */
	public function default_page_class_is_instantiated_when_specific_match_is_not_found()
	{
		$storyblokMock = $this->mockPage('Default');

		$storyblokMock->bySlug('use_default'); // must have no matching class

		$class = $storyblokMock->read();

		$this->assertInstanceOf('Testcomponents\Storyblok\DefaultPage', $class);
	}


	/** @test */
	public function specific_page_class_is_instantiated()
	{
		$storyblokMock = $this->mockPage('Specific');

		$storyblokMock->bySlug('specific');
		$class = $storyblokMock->read();

		$this->assertInstanceOf('Testcomponents\Storyblok\SpecificPage', $class);
	}


	/** @xxxxxxtest */
	public function can_use_page_title_from_config_file()
	{
		$storyblokMock = $this->mockPage('Specific');

		$storyblokMock->bySlug('use_default'); // must have no matching class
		$class = $storyblokMock->read();

		$this->assertEquals('Default title from config', $class->title());
	}

	/** @test */
	public function can_read_page_title_from_seo()
	{
		$storyblokMock = $this->mockPage();
		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		$this->assertEquals('SEO title', $class->title());
	}

	/** @test */
	public function can_read_page_description_from_seo()
	{
		$storyblokMock = $this->mockPage();
		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		$this->assertEquals('SEO description', $class->metaDescription());
	}

	/** @test */
	public function can_read_block_content()
	{
		$storyblokMock = $this->mockPage();
		$storyblokMock->bySlug('use_default');
		$class = $storyblokMock->read();

		// content() returns the first block of this page, we’ll ask for the ‘title’ property
		$this->assertEquals('Block Title', $class->content()->title);
	}

	/** @test */
	public function can_apply_block_content_mutation()
	{
		$storyblokMock = $this->mockPage();
		$storyblokMock->bySlug('use_default');
		$class = $storyblokMock->read();

		// content() returns the first block of this page, we’ll ask for the ‘title’ property
		$this->assertEquals('BLOCK TITLE', $class->content()->subtitle);
	}

	/** @test */
	public function can_specify_timestamps()
	{
		$storyblokMock = $this->mockPage('Date');
		$storyblokMock->bySlug('date-block');
		$class = $storyblokMock->read();

		$this->assertInstanceOf('Carbon\Carbon', $class->content()->schedule);
	}

	/** @test */
	public function can_apply_typographic_fixes()
	{
		$storyblokMock = $this->mockPage('TraitBlock');

		$storyblokMock->bySlug('specific');
		$class = $storyblokMock->read();

		$this->assertEquals('<span class="pull-double">“</span>Lorem ipsum dolor sit amet, consectetur adipiscing elit”. <span class="numbers">3</span>&nbsp;x&nbsp;<span class="numbers">4</span>.', $class->content()->typography);

	}

	/** @test */
	public function can_apply_custom_typographic_styles()
	{
		$storyblokMock = $this->mockPage('Trait2Block');

		$storyblokMock->bySlug('specific');
		$class = $storyblokMock->read();

		$this->assertEquals('<span class="pull-double">“</span>Lorem ipsum dolor sit amet, con­secte­tur adip­isc­ing elit”. 3&nbsp;x 4.', $class->content()->typography);

	}

	/** @test */
	public function can_use_default_page_view()
	{
		$storyblokMock = $this->mockPage('Default');

		$storyblokMock->bySlug('use_default'); // must have no matching class
		$class = $storyblokMock->read();

		$this->assertEquals('default view', $class->render()->render());
	}

	/** @test */
	public function can_use_specific_page_view()
	{
		$storyblokMock = $this->mockPage('Specific');

		$storyblokMock->bySlug('specific');
		$class = $storyblokMock->read();

		$this->assertEquals('specific view', $class->render()->render());
	}

	/** // @test */
	/*
	 * I don’t know how to mock the response to stop it calling the API
	 * */
	public function can_load_child_responses()
	{
		$mock = \Mockery::mock('overload:Testcomponents\Storyblok\ChildrenBlock')->makePartial();
		$mock->shouldReceive('childStory')->andReturn('I don’t know......');

		$storyblokMock = $this->mockPage('HasChildBlock');

		$storyblokMock->bySlug('has-child');
		$class = $storyblokMock->read();

		$this->assertInstanceOf('Testcomponents\Storyblok\ChildrenBlock', $class->content()->children); // test property of child
	}

	/** @test */
	public function can_filter_child_components()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		$type1 = $class->content()->intro->filterComponent('text_with_links');
		$type2 = $class->content()->intro->filterComponent('award');

		$this->assertEquals($type1->count(), 2); // two sets of text_with_links
		$this->assertEquals($type2->count(), 1); // 1 award
	}

	/** @test */
	public function component_name_is_set_in_block()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		$this->assertEquals($class->content()->intro->component(), 'intro'); // 1 award
	}

	/** @test */
	public function component_content_is_iteratable()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		foreach ($class->content()->intro[0]->links as $link) {
			$this->assertEquals($link->component(), 'text_link');
		}
	}

	/** @test */
	public function component_content_has_array_access()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		$this->assertEquals($class->content()->intro[0]->links[2]->url->cached_url, 'services/third');
	}
}
