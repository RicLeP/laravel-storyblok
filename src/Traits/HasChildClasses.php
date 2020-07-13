<?php


namespace Riclep\Storyblok\Traits;


use Illuminate\Support\Str;

trait HasChildClasses
{
	private function getChildClassName($type, $name) {
		if (class_exists(config('storyblok.component_class_namespace') . Str::pluralStudly($type) . '\\' . Str::studly($name))) {
			return config('storyblok.component_class_namespace') . Str::pluralStudly($type) . '\\' . Str::studly($name);
		}

		if (class_exists(config('storyblok.component_class_namespace') . Str::studly($type))) {
			return config('storyblok.component_class_namespace') . Str::studly($type);
		}
	}
}