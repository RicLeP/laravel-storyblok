<?php


namespace Riclep\Storyblok\Traits;


use Illuminate\Support\Str;
use Riclep\Storyblok\Block;

trait CssClasses
{
	public function cssClassWithParent()
	{
		return $this->component() . '@' . $this->getAncestorComponent(1);
	}

	public function cssClassWithLayout()
	{
		if ($layout = $this->getLayout()) {
			return $this->component() . '@' . $layout;
		}
	}


	public function cssClass()
	{
		return $this->component();
	}

	public function isLayout()
	{
		return $this->layoutCheck($this->component());
	}

	public function getLayout()
	{
		$path = $this->componentPath;
		array_pop($path);
		$path = array_reverse($path);

		foreach ($path as $ancestor) {
			if ($this->layoutCheck($ancestor)) {
				return $ancestor;
			}
		}

		return null;
	}

	private function layoutCheck($componentName) {
		return Str::startsWith($componentName, 'layout_');
	}


	/*
	functions:

	full path
	first layout


	 * */



}