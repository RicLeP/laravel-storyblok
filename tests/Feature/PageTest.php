<?php

namespace Riclep\Storyblok\Tests\Feature;


use Riclep\Storyblok\Storyblok;
use Riclep\Storyblok\Tests\TestCase;


class PageTest extends TestCase
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

		$this->assertInstanceOf('Testcomponents\Storyblok\Pages\Specific', $class);
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
		$storyblokMock = $this->mockPage('Trait1');

		$storyblokMock->bySlug('specific');
		$class = $storyblokMock->read();

		$this->assertEquals('<span class="pull-double">“</span>Lorem ipsum dolor sit amet, consectetur adipiscing elit”. <span class="numbers">3</span>&nbsp;x&nbsp;<span class="numbers">4</span>.', $class->content()->typography);

	}

	/** @test */
	public function can_apply_custom_typographic_styles()
	{
		$storyblokMock = $this->mockPage('Trait2');



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
		$mock = \Mockery::mock('overload:Testcomponents\Storyblok\Blocks\Children')->makePartial();
		$mock->shouldReceive('childStory')->andReturn('I don’t know......');

		$storyblokMock = $this->mockPage('HasChild');

		$storyblokMock->bySlug('has-child');
		$class = $storyblokMock->read();

		$this->assertInstanceOf('Testcomponents\Storyblok\Blocks\Children', $class->content()->children); // test property of child
	}

	/** @test */
	public function can_filter_child_components()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		$type1 = $class->content()->layout_intro->filterComponent('text_with_links');
		$type2 = $class->content()->layout_intro->filterComponent('award');

		$this->assertEquals($type1->count(), 2); // two sets of text_with_links
		$this->assertEquals($type2->count(), 1); // 1 award
	}

	/** @test */
	public function component_name_is_set_in_block()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		$this->assertEquals($class->content()->layout_intro->component(), 'layout_intro'); // 1 award
	}

	/** @test */
	public function component_content_is_iteratable()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		foreach ($class->content()->layout_intro[0]->links as $link) {
			$this->assertEquals($link->component(), 'text_link');
		}
	}

	/** @test */
	public function component_content_has_array_access()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		$this->assertEquals($class->content()->layout_intro[0]->links[2]->url->cached_url, 'services/third');
	}

	/** @test */
	public function block_has_component_path()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		$path = ['homepage', 'layout_intro', 'text_with_links', 'links', 'text_link', 'url'];

		$this->assertEquals($class->content()->layout_intro[0]->links[2]->url->componentPath(), $path);
	}

	/** @test */
	public function check_block_is_child()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		// $path = ['homepage', 'layout_intro', 'text_with_links', 'links', 'text_link', 'url'];

		$this->assertTrue($class->content()->layout_intro[0]->links[2]->url->isChildOf('text_link'));
		$this->assertFalse($class->content()->layout_intro[0]->links[2]->url->isChildOf('links'));
	}

	/** @test */
	public function check_block_is_ancestor()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		// $path = ['homepage', 'layout_intro', 'text_with_links', 'links', 'text_link', 'url'];

		$this->assertTrue($class->content()->layout_intro[0]->links[2]->url->isAncestorOf('text_with_links'));
		$this->assertFalse($class->content()->layout_intro[0]->links[2]->isAncestorOf('url'));
	}

	/** @test */
	public function check_block_is_in_layout()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		// $path = ['homepage', 'layout_intro', 'text_with_links', 'links', 'text_link', 'url'];

		$this->assertEquals($class->content()->layout_intro[0]->links[2]->url->getLayout(), 'layout_intro');
		$this->assertNotEquals($class->content()->layout_intro[0]->links[2]->url->getLayout(), 'layout_not');
	}

	/** @test */
	public function check_block_is_layout()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		// $path = ['homepage', 'layout_intro', 'text_with_links', 'links', 'text_link', 'url'];

		$this->assertTrue($class->content()->layout_intro->isLayout());
		$this->assertFalse($class->content()->layout_intro[0]->links[2]->url->isLayout());
	}

	/** @test */
	public function get_css_class()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		// $path = ['homepage', 'layout_intro', 'text_with_links', 'links', 'text_link', 'url'];

		$this->assertEquals($class->content()->layout_intro[0]->links->cssClass(), 'links');

	}

	/** @test */
	public function get_css_class_with_parent()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		// $path = ['homepage', 'layout_intro', 'text_with_links', 'links', 'text_link', 'url'];

		$this->assertEquals($class->content()->layout_intro[0]->links->cssClassWithParent(), 'links@text_with_links');
	}

	/** @test */
	public function get_css_class_with_layout()
	{
		$storyblokMock = $this->mockPage('Complex');

		$storyblokMock->bySlug('use_default');

		$class = $storyblokMock->read();

		// $path = ['homepage', 'layout_intro', 'text_with_links', 'links', 'text_link', 'url'];

		$this->assertEquals($class->content()->layout_intro[0]->links->cssClassWithLayout(), 'links@layout_intro');
		$this->assertEquals($class->content()->cssClassWithLayout(), 'homepage'); // no layout
	}
}
