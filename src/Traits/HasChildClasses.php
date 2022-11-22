<?php


namespace Riclep\Storyblok\Traits;


use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasChildClasses
{
	/**
	 * A general method for finding child classes such as nested Blocks and Fields.
	 * Tries to match them based on their type and name
	 *
	 * @param $type
	 * @param $name
	 * @return string|null
	 */
	public function getChildClassName($type, $name): ?string
	{
		foreach (Arr::wrap(config('storyblok.component_class_namespace')) as $namespace) {
			if (class_exists($namespace . Str::pluralStudly($type) . '\\' . Str::studly($name))) {
				return $namespace . Str::pluralStudly($type) . '\\' . Str::studly($name);
			}
		}

		foreach (Arr::wrap(config('storyblok.component_class_namespace')) as $namespace) {
			if (class_exists($namespace . Str::studly($type))) {
				return $namespace . Str::studly($type);
			}
		}

		return null;
	}
}