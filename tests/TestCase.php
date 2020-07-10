<?php

namespace Riclep\Storyblok\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Riclep\Storyblok\Page;

class TestCase extends Orchestra
{
	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 * @return void
	 */
	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('storyblok.component_class_namespace', 'Riclep\Storyblok\Tests\Fixtures\\');
		$app['config']->set('storyblok.view_path');

		$app['config']->set('seo.default_title', 'Default title from config');
		$app['config']->set('seo.default_description', 'Default description from config');

		$viewPath = str_replace('..', '', __DIR__ . DIRECTORY_SEPARATOR);

		$app['config']->set('view.paths', array_merge(config('view.paths'), [$viewPath]));
	}

	protected function makePage($file = null) {
		$story = json_decode(file_get_contents(__DIR__ . '/Fixtures/' . ($file ?: 'all-fields.json')), true);

		return new Page($story['story']);
	}

}
