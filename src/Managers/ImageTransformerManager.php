<?php

namespace Riclep\Storyblok\Managers;

use Riclep\Storyblok\Support\ImageDrivers\Img2Storyblok;
use Riclep\Storyblok\Support\ImageDrivers\Storyblok;

class ImageTransformerManager extends \Illuminate\Support\Manager
{

	/**
	 * @inheritDoc
	 */
	public function getDefaultDriver()
	{
		return 'storyblok';
	}

	// storyblok
	public function createStoryblokDriver() {
		return new Storyblok();
	}

	// img2-storyblok
	public function createImg2StoryblokDriver() {
		return new Img2Storyblok();
	}
}