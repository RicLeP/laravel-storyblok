<?php

namespace Riclep\Storyblok\Tests;

use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionClass;
use Riclep\Storyblok\StoryblokServiceProvider;



class TestCase extends Orchestra
{


	protected function getPackageProviders($app)
	{
		return [StoryblokServiceProvider::class];
	}

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

		if ($file) {
			$class = config('storyblok.component_class_namespace') . 'Pages\\' . Str::studly($story['story']['content']['component']);
		} else {
			$class = config('storyblok.component_class_namespace') . 'Page';
		}

		return new $class($story['story']);
	}

	public static function callMethod($obj, $name, array $args = []) {
		$class = new ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}
}
