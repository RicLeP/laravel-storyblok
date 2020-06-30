<?php


namespace Riclep\Storyblok\Traits;


use Illuminate\Support\Str;
use Riclep\Storyblok\Block;

trait CssClasses
{
	private $layoutPrefix = 'layout_';

	/**
	 * Returns a string matching the current component’s name
	 * and it’s parent’s component name in the following format:
	 * child@parent
	 *
	 * @return string
	 */
	public function cssClassWithParent()
	{
		return $this->component() . '@' . $this->getAncestorComponent(1);
	}

	/**
	 * Returns the current component’s name along with the that of
	 * the first ‘layout’ ancestor component found in this format:
	 * component@layout_ancestor
	 * By default a layout component is prefixed layout_
	 *
	 * @return string
	 */
	public function cssClassWithLayout()
	{
		if ($layout = $this->getLayout()) {
			return $this->component() . '@' . $layout;
		}

		return $this->component();
	}


	/**
	 * Returns the CSS class for the current component
	 *
	 * @return string
	 */
	public function cssClass()
	{
		return $this->component();
	}

	/**
	 * Checks if the current component is a layout
	 *
	 * @return bool
	 */
	public function isLayout()
	{
		return $this->layoutCheck($this->component());
	}

	/**
	 * Checks all the ancestor components to find the first
	 * matching layout
	 *
	 * @return string|null
	 */
	public function getLayout()
	{
		$path = $this->componentPath();
		array_pop($path);
		$path = array_reverse($path);

		foreach ($path as $ancestor) {
			if ($this->layoutCheck($ancestor)) {
				return $ancestor;
			}
		}

		return null;
	}

	/**
	 * Checks a component to see if it matches the requirement
	 * to be defined as a layout. By default this is any
	 * starting with _layout
	 *
	 * @param $componentName
	 * @return bool
	 */
	private function layoutCheck($componentName) {
		return Str::startsWith($componentName, $this->layoutPrefix);
	}
}