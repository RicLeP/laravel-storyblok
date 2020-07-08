<?php


namespace Riclep\Storyblok\Traits;

trait xRenderable
{
	/**
	 * Returns an array of possible views for rendering this Block’s content with
	 * It checks the URL of the current request and matches this to the folder
	 * structure of the views
	 *
	 * @return array
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	protected function views() {
		$views = [];

		$views[] = config('storyblok.view_path') . 'blocks.uuid.' . $this->_uid;
		$segments = explode('/', rtrim(app()->make('Page')->slug(), '/'));
		// creates an array of dot paths for each path segment
		// site.com/this/that/them becomes:
		// this.that.them
		// this.that
		// this
		$views[] = config('storyblok.view_path') . 'blocks.' . implode('.', $segments) . '.=' . $this->component;
		$views[] = config('storyblok.view_path') . 'blocks.' . implode('.', $segments) . '.' . $this->component;
		while (count($segments) > 1) {
			array_pop($segments);
			$views[] = config('storyblok.view_path') . 'blocks.' . implode('.', $segments) . '.' . $this->component;
		}

		$views[] = config('storyblok.view_path') . 'blocks.' . $this->component;
		$views[] = config('storyblok.view_path') . 'blocks.default';

		return $views;
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