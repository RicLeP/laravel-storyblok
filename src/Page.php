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
		$views[] = 'storyblok.pages.' . implode('.', $segments);

		// creates an array of dot paths for each path segment
		// site.com/this/that/them becomes:
		// this.that.them
		// this.that
		// this
		while (count($segments) >= 1) {
			// singular views next so we match children with a folder
			if (!in_array($path = 'storyblok.pages.' . Str::singular(implode('.', $segments)), $views)) {
				$views[] = 'storyblok.pages.' . Str::singular(implode('.', $segments));
			}

			if (!in_array($path = 'storyblok.pages.' . implode('.', $segments), $views)) {
				$views[] = $path;
			}

			array_pop($segments);
		}

		$views[] = config('storyblok.view_path') . 'pages.default';

		return $views;
	}


	public function view() {
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