<?php


namespace Riclep\Storyblok\Traits;

trait Renderable
{
	protected function views() {
		$views[] = 'storyblok.blocks.uuid.' . $this->_uid;
		$segments = explode('/', rtrim(app()->make('Page')->slug(), '/'));
		// creates an array of dot paths for each path segment
		// site.com/this/that/them becomes:
		// this.that.them
		// this.that
		// this
		$views[] = 'storyblok.blocks.' . implode('.', $segments) . '.=' . $this->component;
		$views[] = 'storyblok.blocks.' . implode('.', $segments) . '.' . $this->component;
		while (count($segments) > 1) {
			array_pop($segments);
			$views[] = 'storyblok.blocks.' . implode('.', $segments) . '.' . $this->component;
		}

		$views[] = 'storyblok.blocks.' . $this->component;
		$views[] = 'storyblok.blocks.default';

		return $views;
	}

	/**
	 * Returns the first matching view
	 *
	 * @return bool|mixed
	 */
	public function view() {
		foreach ($this->views() as $view) {
			$view = view()->exists($view) ? $view : false;
			break;
		}

		return $view;
	}

	/**
	 * Finds the view used to display this block’s content
	 * it will always fall back to a default view.
	 *
	 */
	public function render()
	{
		return view()->first(
			$this->views(),
			[
				'block' => $this
			]
		);
	}
}