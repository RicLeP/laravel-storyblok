<?php


namespace Riclep\Storyblok\Tests\Fixtures\Fields;


use Riclep\Storyblok\Fields\Image;

class HeroImage extends Image
{

	public function transformations() {
		$this->transformations = [
			'desktop' => [
				'src' => $this->transform()->resize(500, 400),
				'media' => '(min-width: 1200px)',
			],
			'mobile' => [
				'src' => $this->transform()->resize(100, 120)->format('webp'),
				'media' => '(min-width: 600px)',
			],
		];
	}
}