<?php


namespace Riclep\Storyblok;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Riclep\Storyblok\Traits\ProcessesBlocks;

abstract class Page
{
	use ProcessesBlocks;

	private $processedJson;
	private $content;
	private $seo;
	protected $title;

	public function __construct($rawStory)
	{
		$this->processedJson = $rawStory;
	}

	/**
	 * Performs any actions on the Storyblok content before it is parsed into Block classes
	 * Move SEO plugin out of content to the root of the page’s response
	 */
	public function preprocess() {
		if (array_key_exists('seo', $this->processedJson['content'])) {
			$this->processedJson['seo'] = $this->processedJson['content']['seo'];
			unset($this->processedJson['content']['seo']);

			$this->seo = $this->processedJson['seo'];
		}


		return $this;
	}

	/**
	 * Processes the page’s meta content
	 */
	public function process() {
		return $this;
	}

	/**
	 * Perform actions on the data after all blocks have been prepared
	 *
	 * @return void
	 */
	public function postProcess()
	{
		$this->content()->makeComponentPath([]);

		foreach (class_uses_recursive($this) as $trait) {
			if (method_exists($this, $method = 'init' . class_basename($trait))) {
				$this->{$method}();
			}
		}

		return $this;
	}

	public function getBlocks() {
		$this->content = $this->processBlock($this->processedJson['content'], 'root');

		return $this;
	}

	/**
	 * @return array
	 */
	protected function views() {
		$views = [];

		//$viewFile = strtolower(subStr((new \ReflectionClass($this))->getShortName(), 0, -4));

		$segments = explode('/', rtrim($this->slug(), '/'));

		// match full path first
		$views[] = config('storyblok.view_path') . 'pages.' . implode('.', $segments);

		// creates an array of dot paths for each path segment
		// site.com/this/that/them becomes:
		// this.that.them
		// this.that
		// this
		while (count($segments) >= 1) {
			if (!in_array($path = config('storyblok.view_path') . 'pages.' . implode('.', $segments), $views)) {
				$views[] = config('storyblok.view_path') . 'pages.' . implode('.', $segments) . '.' . $this->content()->component();
				$views[] = $path;
			}

			array_pop($segments);
		}

		if (!in_array($path = config('storyblok.view_path') . 'pages.' . $this->content()->component(), $views)) {
			$views[] = config('storyblok.view_path') . 'pages.' . $this->content()->component();
		}

		$views[] = config('storyblok.view_path') . 'pages.default';

		return $views;
	}


	public function view() {
		//dd($this->views());

		foreach ($this->views() as $view) {
			if (view()->exists($view)) {
				return $view;
			}
		}
	}

	/**
	 * Reads the story
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function render($additionalContent = null) {
		$content = [
			'title' => $this->title(),
			'meta_description' => $this->metaDescription(),
			'story' => $this->content(),
			'seo' => $this->seo,
		];

		if ($additionalContent) {
			$content = array_merge($content, $additionalContent);
		}

		return view($this->view(), $content);
	}

	public function title() {
		if ($this->seo) {
			return $this->seo['title'];
		}

		return $this->processedJson['name'];
	}

	public function metaDescription() {
		if ($this->seo) {
			return $this->seo['description'];
		}

		return  config('seo.default-description');
	}

	public function content() {
		return $this->content;
	}

	public function slug()
	{
		return $this->processedJson['full_slug'];
	}
}