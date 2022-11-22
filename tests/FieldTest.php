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
use Riclep\Storyblok\Fields\Table;
use Riclep\Storyblok\Fields\Textarea;
use Riclep\Storyblok\Fields\UrlLink;
use Riclep\Storyblok\Support\ImageTransformers\Storyblok;
use Riclep\Storyblok\Tests\Fixtures\Blocks\NullBlock;
use Riclep\Storyblok\Tests\Fixtures\Fields\AssetWithAccessor;
use Riclep\Storyblok\Tests\Fixtures\Fields\HeroImage;
use Riclep\Storyblok\Tests\Fixtures\Fields\Imgix;
use Riclep\Storyblok\Tests\Fixtures\Fields\WithImage;

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
	public function can_convert_blocks_in_rich_text()
	{
		$story = json_decode(file_get_contents(__DIR__ . '/Fixtures/richtext.json'), true);
		$content =  $story['story']['content']['body'];

		config(['storyblok.view_path' => 'Fixtures.views.']);

	//	dd($content);

		$field = new RichText($content, new NullBlock([], null));

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\Person', $field->content()[2]);
		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Block', $field->content()[3]);
		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Blocks\Person', $field->content()[4]);
	}

	/** @test */
	public function can_convert_blocks_in_rich_text_to_html()
	{
		$story = json_decode(file_get_contents(__DIR__ . '/Fixtures/richtext.json'), true);
		$content =  $story['story']['content']['body'];

		config(['storyblok.view_path' => 'Fixtures.views.']);

		$field = new RichText($content, new NullBlock([], null));

		$this->assertEquals('<p>hello some copy</p><p><b>and here </b></p>this is a person called testthis is a buttonthis is a person called name<p>lookkkkk</p>', (string) $field);
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
	public function can_get_image_asset_as_url_with_custom_domain()
	{
		config()->set('storyblok.asset_domain', 'custom.asset.domain');
		$field = new Image($this->getFieldContents('asset_image'), null);

		$this->assertEquals('https://custom.asset.domain/f/52681/700x700/97f51f6374/blood-cells.jpg', (string) $field);
	}

	/** @test */
	public function can_get_image_asset_dimensions()
	{
		$field = new Image($this->getFieldContents('hero'), null);

		$this->assertEquals(960, $field->width());
		$this->assertEquals(1280, $field->height());
	}

	/** @test */
	public function can_get_original_image_url()
	{
		$field = new Image($this->getFieldContents('hero'), null);

		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg', (string) $field);
	}

	/** @test */
	public function can_resize_image()
	{
		$field = new Image($this->getFieldContents('hero'), null);
		$field = $field->transform()->resize(234, 432);

		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/234x432', (string) $field);

		$field2 = new Image($this->getFieldContents('hero'), null);
		$field2 = $field2->transform()->resize(800, 200, 'smart');

		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/800x200/smart', (string) $field2);

		$field2 = new Image($this->getFieldContents('image'), null);
		$field2 = $field2->transform()->resize(800, 200, 'focal-point');

		$this->assertEquals('https://a.storyblok.com/f/96945/1600x793/c55f649622/2020-ar-fusionacusoft-letterbox-2.jpg/m/800x200/filters:focal(350x426:351x427)', (string) $field2);
	}

	/** @test */
	public function can_resize_image_with_legacy_driver()
	{
		config()->set('storyblok.image_transformer', \Riclep\Storyblok\Support\ImageTransformers\StoryblokLegacy::class);
		config()->set('storyblok.image_service_domain', 'img2.storyblok.com');

		$field = new Image($this->getFieldContents('hero'), null);
		$field = $field->transform()->resize(234, 432);

		$this->assertEquals('//img2.storyblok.com/234x432/f/87028/960x1280/31a1d8dc75/bottle.jpg', (string) $field);

		$field2 = new Image($this->getFieldContents('hero'), null);
		$field2 = $field2->transform()->resize(800, 200, 'smart');

		$this->assertEquals('//img2.storyblok.com/800x200/smart/f/87028/960x1280/31a1d8dc75/bottle.jpg', (string) $field2);

		$field2 = new Image($this->getFieldContents('image'), null);
		$field2 = $field2->transform()->resize(800, 200, 'focal-point');

		$this->assertEquals('//img2.storyblok.com/800x200/filters:focal(350x426:351x427)/f/96945/1600x793/c55f649622/2020-ar-fusionacusoft-letterbox-2.jpg', (string) $field2);
	}

	/** @test */
	public function can_set_image_format()
	{
		$field = new Image($this->getFieldContents('hero'), null);
		$field = $field->transform()->format('png');

		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/filters:format(png)', (string) $field);

		$field2 = new Image($this->getFieldContents('hero'), null);
		$field2 = $field2->transform()->format('jpg', 10);

		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/filters:format(jpg):quality(10)', (string) $field2);

		$field3 = new Image($this->getFieldContents('hero'), null);
		$field3 = $field3->transform()->format('jpg', 10)->resize(10, 10);

		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/10x10/filters:format(jpg):quality(10)', (string) $field3);
	}

	/** @test */
	public function can_fit_image()
	{
		$field = new Image($this->getFieldContents('hero'), null);
		$field = $field->transform()->fitIn(400, 400, 'ff0000');

		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/fit-in/400x400/filters:fill(ff0000)', (string) $field);

		$field2 = new Image($this->getFieldContents('hero'), null);
		$field2 = $field2->transform()->fitIn(400, 400, 'transparent');

		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/fit-in/400x400/filters:format(webp):fill(transparent)', (string) $field2);

		$field3 = new Image($this->getFieldContents('hero'), null);
		$field3 = $field3->transform()->fitIn(400, 400, 'transparent')->format('webp');

		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/fit-in/400x400/filters:format(webp):fill(transparent)', (string) $field3);
	}

	/** @test */
	public function can_use_custom_image_service_domain()
	{
		config()->set('storyblok.image_service_domain', 'custom.imageservice.domain');

		$field = new Image($this->getFieldContents('hero'), null);
		$field = $field->transform()->fitIn(400, 400, 'ff0000');

		$this->assertEquals('https://custom.imageservice.domain/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/fit-in/400x400/filters:fill(ff0000)', (string) $field);
	}

	/** @test */
	public function can_use_custom_image_service_domain_with_legacy_driver()
	{
		config()->set('storyblok.image_transformer', \Riclep\Storyblok\Support\ImageTransformers\StoryblokLegacy::class);
		config()->set('storyblok.image_service_domain', 'custom.imageservice.domain');

		$field = new Image($this->getFieldContents('hero'), null);
		$field = $field->transform()->fitIn(400, 400, 'ff0000');

		$this->assertEquals('//custom.imageservice.domain/fit-in/400x400/filters:fill(ff0000)/f/87028/960x1280/31a1d8dc75/bottle.jpg', (string) $field);
	}

	/** @test */
	public function can_get_image_details()
	{
		$field = new Image($this->getFieldContents('hero'), null);

		$this->assertEquals(960, $field->width());
		$this->assertEquals(1280, $field->height());
		$this->assertEquals('image/jpeg', $field->mime());

		$field1 = $field->transform()->format('png');

//		dd($field1);

		$this->assertEquals(960, $field1->width());
		$this->assertEquals(1280, $field1->height());
		$this->assertEquals('image/png', $field1->mime());

		$field2 = $field->transform()->resize(100, 200)->format('png');

		$this->assertEquals(100, $field2->width());
		$this->assertEquals(200, $field2->height());

		// test we can read the original dimensions
		$this->assertEquals(960, $field2->width(true));
		$this->assertEquals(1280, $field2->height(true));
		$this->assertEquals('image/jpeg', $field2->mime(true));
	}

	/** @test */
	public function transparent_filled_images_have_correct_format()
	{
		$field = new Image($this->getFieldContents('hero'), null);
		$field = $field->transform()->fitIn(10, 10, 'transparent');

		$this->assertEquals('image/webp', $field->mime());


		$field2 = new Image($this->getFieldContents('hero'), null);
		$field2 = $field2->transform()->fitIn(10, 10, 'transparent')->format('webp');

		$this->assertEquals('image/webp', $field2->mime());
	}


	/** @test */
	public function can_use_a_named_transformation()
	{

		$field = new HeroImage($this->getFieldContents('hero'), null);
		$field->transform('mobile');

		$this->assertInstanceOf(Storyblok::class, $field->transform('mobile')['src']);
		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/100x120/filters:format(webp)', (string) $field->transform('mobile')['src']);
		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/100x120/filters:format(png)', (string) $field->transform('mobile')['src']->format('png'));
		$this->assertEquals('https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/100x120/filters:format(png)', (string) $field->transform('mobile'));
	}


	/** @test */
	public function can_get_create_picture_elements()
	{
		$field = new HeroImage($this->getFieldContents('hero'), null);

		$this->assertEquals(<<<'PICTURE'
<picture>
<source srcset="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/500x400" type="image/jpeg" media="(min-width: 1200px)">
<source srcset="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/100x120/filters:format(webp)" type="image/webp" media="(min-width: 600px)">

<img src="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg" alt="Some alt text with &quot;" >
</picture>
PICTURE
			, str_replace("\t", '', $field->picture('Some alt text with "')));

		$this->assertEquals(<<<'PICTURE'
<picture>
<source srcset="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/500x400" type="image/jpeg" media="(min-width: 1200px)">

<img src="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/100x120/filters:format(webp)" alt="Some alt text with &quot;" >
</picture>
PICTURE
			, str_replace("\t", '', $field->picture('Some alt text with "', 'mobile')));

		$this->assertEquals(<<<'PICTURE'
<picture>
<source srcset="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/500x400" type="image/jpeg" media="(min-width: 1200px)">

<img src="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/100x120/filters:format(webp)" alt="Some alt text with &quot;"  class="laravel storyblok"  id="some-id" >
</picture>
PICTURE
			, str_replace("\t", '', $field->picture('Some alt text with "', 'mobile', ['class' => 'laravel storyblok', 'id' => 'some-id'])));
	}

	/** @test */
	public function can_set_picture_element_transforms()
	{
		$field = new HeroImage($this->getFieldContents('hero'), null);

		$this->assertEquals(<<<'PICTURE'
<picture>
<source srcset="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/200x200/filters:format(webp)" type="image/webp" media="(min-width: 400px)">
<source srcset="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/500x400" type="image/jpeg" media="(min-width: 1200px)">

<img src="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg" alt="Some alt text with &quot;" >
</picture>
PICTURE
			, str_replace("\t", '', $field->setTransformations([
				'mobile' => [
					'src' => $field->transform()->resize(200, 200)->format('webp'),
					'media' => '(min-width: 400px)',
				],
				'desktop' => [
					'src' => $field->transform()->resize(500, 400),
					'media' => '(min-width: 1200px)',
				],
			])->picture('Some alt text with "')));

		$this->assertEquals(<<<'PICTURE'
<picture>
<source srcset="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/400x400" type="image/jpeg" media="(min-width: 800px)">

<img src="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/200x200/filters:format(webp)" alt="Some alt text with &quot;" >
</picture>
PICTURE
			, str_replace("\t", '', $field->setTransformations([
				'mobile' => [
					'src' => $field->transform()->resize(200, 200)->format('webp'),
					'media' => '(min-width: 400px)',
				],
				'desktop' => [
					'src' => $field->transform()->resize(400, 400),
					'media' => '(min-width: 800px)',
				],
			])->picture('Some alt text with "', 'mobile')));
	}

	/** @test */
	public function can_create_transform_as_new_instance()
	{
		$field = new HeroImage($this->getFieldContents('hero'), null);

		$newInstance = $field->setTransformations([
			'mobile' => [
				'src' => $field->transform()->resize(200, 200)->format('webp'),
				'media' => '(min-width: 400px)',
			]
		], false);

		$this->assertInstanceOf(HeroImage::class, $newInstance);
		$this->assertNotEquals($newInstance, $field);
	}

	/** @test */
	public function can_get_create_picture_element_with_custom_domains()
	{
		config()->set('storyblok.asset_domain', 'custom.asset.domain');
		config()->set('storyblok.image_service_domain', 'custom.imageservice.domain');

		$field = new HeroImage($this->getFieldContents('hero'), null);

		$this->assertEquals(<<<'PICTURE'
<picture>
<source srcset="https://custom.imageservice.domain/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/500x400" type="image/jpeg" media="(min-width: 1200px)">
<source srcset="https://custom.imageservice.domain/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/100x120/filters:format(webp)" type="image/webp" media="(min-width: 600px)">

<img src="https://custom.asset.domain/f/87028/960x1280/31a1d8dc75/bottle.jpg" alt="Some alt text with &quot;" >
</picture>
PICTURE
, str_replace("\t", '', $field->picture('Some alt text with "')));
	}


	/** @test */
	public function can_create_img_srcset()
	{
		$field = new HeroImage($this->getFieldContents('hero'), null);

		$this->assertEquals(<<<'SRCSET'
<img srcset=" https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/500x400 500w,  https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/100x120/filters:format(webp) 100w, " sizes="  (min-width: 1200px) 500px,    (min-width: 600px) 100px,  " src="https://a.storyblok.com/f/87028/960x1280/31a1d8dc75/bottle.jpg" alt="Some alt text with &quot;" >
SRCSET
			, str_replace("\t", '', $field->srcset('Some alt text with "')));
	}

	/** @test */
	public function can_create_img_srcset_with_custom_domains()
	{
		config()->set('storyblok.asset_domain', 'custom.asset.domain');
		config()->set('storyblok.image_service_domain', 'custom.imageservice.domain');

		$field = new HeroImage($this->getFieldContents('hero'), null);

		$this->assertEquals(<<<'SRCSET'
<img srcset=" https://custom.imageservice.domain/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/500x400 500w,  https://custom.imageservice.domain/f/87028/960x1280/31a1d8dc75/bottle.jpg/m/100x120/filters:format(webp) 100w, " sizes="  (min-width: 1200px) 500px,    (min-width: 600px) 100px,  " src="https://custom.asset.domain/f/87028/960x1280/31a1d8dc75/bottle.jpg" alt="Some alt text with &quot;" >
SRCSET
			, str_replace("\t", '', $field->srcset('Some alt text with "')));
	}



	/** @test */
	public function can_use_imgix_driver()
	{
		config()->set('storyblok.image_transformer', \Riclep\Storyblok\Support\ImageTransformers\Imgix::class);
		config()->set('storyblok.imgix_domain', 'bwi.imgix.net');
		config()->set('storyblok.imgix_token', 'aGd45SE3kRWezggD');

		$field = new Image($this->getFieldContents('hero'), null);
		$field = $field->transform();


		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?ixlib=php-3.3.1&s=6849d7d88c1dddb54b2e0ff9cbb90551', (string) $field);

		$field2 = new Image($this->getFieldContents('hero'), null);
		$field2 = $field2->transform()->resize(200, 200);

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?h=200&ixlib=php-3.3.1&w=200&s=44b0ecde443eb06ac1b4c8e045cdbf3c', (string) $field2);

		$field3 = new Image($this->getFieldContents('hero'), null);
		$field3 = $field3->transform()->resize(200, 200)->fit('min');

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?fit=min&h=200&ixlib=php-3.3.1&w=200&s=324145e9e57535fba295df3e1b293503', (string) $field3);

		$field4 = new Image($this->getFieldContents('hero'), null);
		$field4 = $field4->transform()->resize(200, 200)->fit('fillmax', ['fill-color' => '#f00']);

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?fill-color=%23f00&fit=fillmax&h=200&ixlib=php-3.3.1&w=200&s=139a666211566bebde0cb1ef9ce35231', (string) $field4);

		$field4 = new Image($this->getFieldContents('hero'), null);
		$field4 = $field4->transform()->resize(300, 200)->fit('min')->crop('faces');

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?crop=faces&fit=min&h=200&ixlib=php-3.3.1&w=300&s=861dc469ed701c65668263afc9c0cec8', (string) $field4);

		$field5 = new Image($this->getFieldContents('hero'), null);
		$field5 = $field5->transform()->resize(300, 200)->format('auto');

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?auto=format&h=200&ixlib=php-3.3.1&w=300&s=ad297f938219bb4924dd6862e3ffba35', (string) $field5);

		$field6 = new Image($this->getFieldContents('hero'), null);
		$field6 = $field6->transform()->resize(300, 200)->format('png');

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?fm=png&h=200&ixlib=php-3.3.1&w=300&s=d9c84595ffafea2d8d0f59f49aaf39e2', (string) $field6);

		$field7 = new Image($this->getFieldContents('hero'), null);
		$field7 = $field7->transform()->options(['width' => 100, 'rot' => 46]);

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?ixlib=php-3.3.1&rot=46&width=100&s=b206bb84a1648cc25ff6ba40dcb2b11d', (string) $field7);
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
		$this->assertEquals((string) $field, '');

		$field = new Asset(json_decode(
			'{
				"id": 1223942,
				"alt": null,
				"name": "",
				"focus": null,
				"title": null,
				"copyright": null,
				"fieldtype": "asset"
			}', true
		), null);

		$this->assertFalse($field->hasFile());
	}

	/** @test */
	public function can_check_multi_asset_has_files()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block(); // any parent block will do for testing

		$field = new MultiAsset($this->getFieldContents('multi_assets'), $block);
		$this->assertTrue($field->hasFiles());

		$field = new MultiAsset($this->getFieldContents('multi_assets_empty'), $block);
		$this->assertFalse($field->hasFiles());
	}

	/** @test */
	public function can_use_array_access_on_multi_asset()
	{
		$page = $this->makePage('custom-page.json');
		$block = $page->block(); // any parent block will do for testing

		$field = new MultiAsset($this->getFieldContents('multi_assets'), $block);
		$this->assertEquals('https://a.storyblok.com/f/52681/1000x875/7ced1a10b2/blow-dry-mobile.jpg', $field[0]->filename);

		$this->assertTrue($field->offsetExists(3));
		$this->assertFalse($field->offsetExists(4));

		$this->assertEquals('https://a.storyblok.com/f/52681/700x700/97f51f6374/blood-cells.pdf', (string) $field->offsetGet(1));

		$this->assertEquals(0, $field->key());

		$field->next();

		$this->assertEquals('https://a.storyblok.com/f/52681/700x700/97f51f6374/blood-cells.pdf', (string) $field->current());

		$field->next();
		$this->assertEquals(2, $field->key());

		$field->rewind();
		$this->assertEquals(0, $field->key());

		$this->assertTrue($field->valid());
		$this->assertEquals(4, $field->count());

		$this->assertArrayHasKey(2, $field);
		$field->offsetUnset(2);
		$this->assertArrayNotHasKey(2, $field);
		$this->assertEquals(3, $field->count());

		$field->offsetSet(2, 'hello');
		$this->assertEquals('hello', $field[2]);

		$field->offsetSet(3, 'hello2');
		$this->assertEquals('hello2', $field[3]);

		$field->offsetSet(null, 'hello3');
		$this->assertEquals('hello3', $field[4]);


		$this->assertEquals('https://a.storyblok.com/f/52681/1000x875/7ced1a10b2/blow-dry-mobile.jpg,https://a.storyblok.com/f/52681/700x700/97f51f6374/blood-cells.pdf', (string) $field);
	}

	/** @test */
	public function can_use_array_access_on_multi_asset_with_custom_domain()
	{
		config()->set('storyblok.asset_domain', 'custom.asset.domain');

		$page = $this->makePage('custom-page.json');
		$block = $page->block(); // any parent block will for for testing

		$field = new MultiAsset($this->getFieldContents('multi_assets'), $block);
		$this->assertEquals('https://custom.asset.domain/f/52681/1000x875/7ced1a10b2/blow-dry-mobile.jpg', $field[0]->filename);
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
	public function can_get_story_link_url_with_anchor()
	{
		$field = new StoryLink($this->getFieldContents('link_anchor'), null);
		$this->assertEquals('key-people/primary-contact#the-anchor', (string) $field);
	}

	/** @test */
	public function can_use_custom_field_class()
	{
		$page = $this->makePage();
		$block = $page->block();

		$this->assertInstanceOf('Riclep\Storyblok\Tests\Fixtures\Fields\Hero', $block->hero);
	}

	/** @test */
	public function can_transform_image_url_with_imgix()
	{
		config()->set('storyblok.image_transformer', \Riclep\Storyblok\Support\ImageTransformers\Imgix::class);
		config()->set('storyblok.imgix_domain', 'bwi.imgix.net');
		config()->set('storyblok.imgix_token', 'aGd45SE3kRWezggD');

		$field = new Imgix($this->getFieldContents('imgix'), null);
		$field = $field->transform()->resize(1000, 300);

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fthecuriosityofachild.com%2Fimg%2Flogo-thats-not-canon.png?h=300&ixlib=php-3.3.1&w=1000&s=3e64b50ce487db48093b5d5b3449c156', $field->buildUrl());

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fthecuriosityofachild.com%2Fimg%2Flogo-thats-not-canon.png?h=300&ixlib=php-3.3.1&w=1000&s=3e64b50ce487db48093b5d5b3449c156', (string) $field);
	}

	/** @test */
	public function can_change_image_transformer_to_imgix()
	{
		config()->set('storyblok.imgix_domain', 'bwi.imgix.net');
		config()->set('storyblok.imgix_token', 'aGd45SE3kRWezggD');

		$field = new Image($this->getFieldContents('hero'), null);
		$field = $field->transformer(\Riclep\Storyblok\Support\ImageTransformers\Imgix::class)->transform()->resize(1000, 300);

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?h=300&ixlib=php-3.3.1&w=1000&s=804d97c0c0517b44c0a636737a35b4c7', $field->buildUrl());

		$this->assertEquals('https://bwi.imgix.net/https%3A%2F%2Fa.storyblok.com%2Ff%2F87028%2F960x1280%2F31a1d8dc75%2Fbottle.jpg?h=300&ixlib=php-3.3.1&w=1000&s=804d97c0c0517b44c0a636737a35b4c7', (string) $field);
	}

	/** @test */
	public function can_convert_table_fields_to_html()
	{
		$field = new Table($this->getFieldContents('table'), null);

		$this->assertEquals('<table ><thead><tr><th>title</th><th>title 2</th></tr></thead><tbody><tr><td>value</td><td>value 2</td></tr></tbody></table>', (string) $field);

		$this->assertEquals('<table class="class class--2"><thead><tr><th>title</th><th>title 2</th></tr></thead><tbody><tr><td>value</td><td>value 2</td></tr></tbody></table>', (string) $field->cssClass('class class--2'));


		$field2 = new Table($this->getFieldContents('table'), null);

		$this->assertEquals('<table ><caption>caption</caption><thead><tr><th>title</th><th>title 2</th></tr></thead><tbody><tr><td>value</td><td>value 2</td></tr></tbody></table>', (string) $field2->caption('caption'));

		$this->assertEquals('<table ><caption class="caption class">caption</caption><thead><tr><th>title</th><th>title 2</th></tr></thead><tbody><tr><td>value</td><td>value 2</td></tr></tbody></table>', (string) $field2->caption(['caption', 'caption class']));
	}




	/** @test */
	public function can_set_focal_point()
	{
		// default
		$field = new Image(json_decode(
			'{
				"id": 1223942,
				"alt": null,
				"name": "",
				"focus": "",
				"title": null,
				"filename": "https://a.storyblok.com/f/96945/100x100/c55f649622/2020-ar-fusionacusoft-letterbox-2.jpg",
				"copyright": null,
				"fieldtype": "asset"
			}', true
		), null);

		$this->assertEquals('center', $field->focalPointAlignment());
		$this->assertEquals('left', $field->focalPointAlignment('left'));

		// left top
		$field2 = new Image(json_decode(
			'{
				"id": 1223942,
				"alt": null,
				"name": "",
				"focus": "30x20:31x21",
				"title": null,
				"filename": "https://a.storyblok.com/f/96945/120x120/c55f649622/2020-ar-fusionacusoft-letterbox-2.jpg",
				"copyright": null,
				"fieldtype": "asset"
			}', true
		), null);

		$this->assertEquals('25% 17%', $field2->focalPointAlignment());
		$this->assertEquals('left top', $field2->focalPointAlignment('center', true));


		// center center
		$field3 = new Image(json_decode(
			'{
				"id": 1223942,
				"alt": null,
				"name": "",
				"focus": "41x41:42x42",
				"title": null,
				"filename": "https://a.storyblok.com/f/96945/120x120/c55f649622/2020-ar-fusionacusoft-letterbox-2.jpg",
				"copyright": null,
				"fieldtype": "asset"
			}', true
		), null);

		$this->assertEquals('34% 34%', $field3->focalPointAlignment());
		$this->assertEquals('center center', $field3->focalPointAlignment('center', true));


		// right bottom
		$field4 = new Image(json_decode(
			'{
				"id": 1223942,
				"alt": null,
				"name": "",
				"focus": "88x119:89x120",
				"title": null,
				"filename": "https://a.storyblok.com/f/96945/120x120/c55f649622/2020-ar-fusionacusoft-letterbox-2.jpg",
				"copyright": null,
				"fieldtype": "asset"
			}', true
		), null);

		$this->assertEquals('73% 99%', $field4->focalPointAlignment());
		$this->assertEquals('right bottom', $field4->focalPointAlignment('center', true));
	}


	/** @test */
	public function can_add_with_content()
	{
		// default
		$field = new WithImage(json_decode(
			'{
				"id": 1223942,
				"alt": null,
				"name": "",
				"focus": "",
				"title": null,
				"filename": "https://a.storyblok.com/f/96945/100x100/c55f649622/2020-ar-fusionacusoft-letterbox-2.jpg",
				"copyright": null,
				"fieldtype": "asset"
			}', true
		), null);

		$this->assertEquals('<img src="https://a.storyblok.com/f/96945/100x100/c55f649622/2020-ar-fusionacusoft-letterbox-2.jpg class="some-class">', (string) $field->with(['class' => 'some-class']));
	}

}
