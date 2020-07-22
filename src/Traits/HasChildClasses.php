<?php


namespace Riclep\Storyblok\Traits;


use Illuminate\Support\Str;

trait HasChildClasses
{
	/**
	 * A general method for finding child classes such as nested Blocks and Fields.
	 * Tries to match them based on their type and name
	 *
	 * @param $type
	 * @param $name
	 * @return string
	 */
	private function getChildClassName($type, $name) {
		if (class_exists(config('storyblok.component_class_namespace') . Str::pluralStudly($type) . '\\' . Str::studly($name))) {
			return config('storyblok.component_class_namespace') . Str::pluralStudly($type) . '\\' . Str::studly($name);
		}

		if (class_exists(config('storyblok.component_class_namespace') . Str::studly($type))) {
			return config('storyblok.component_class_namespace') . Str::studly($type);
		}
	}
}