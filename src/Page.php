<?php


namespace Riclep\Storyblok;

use Exception;
use Riclep\Storyblok\Traits\ProcessesBlocks;

abstract class Page
{
	use ProcessesBlocks;

	public $_meta;

	protected $title;

	private $content;
	private $processedJson;

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

			$this->_meta['seo'] = $this->processedJson['seo'];
		}

		return $this;
	}

	/**
	 * Perform actions on the data after all blocks have been prepared
	 *
	 * @return Page
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

	/**
	 * Process the root Block
	 *
	 * @return $this
	 */
	public function getBlocks() {
		$this->content = $this->processBlock($this->processedJson['content'], 'root');

		return $this;
	}


	/**
	 * Returns an array of possible views for the current page
	 *
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


	/**
	 * Returns tne matching view
	 *
	 * @return mixed
	 */
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
			'story' => $this,
		];

		if ($additionalContent) {
			$content = array_merge($content, $additionalContent);
		}

		return view($this->view(), $content);
	}

	/**
	 * Returns the Page’s title
	 *
	 * @return string
	 */
	public function title() {
		if (property_exists($this, 'titleField') && $this->titleField) {
			return strip_tags($this->content[$this->titleField]);
		}

		if ($this->_meta['seo'] && $this->_meta['seo']['title']) {
			return $this->_meta['seo']['title'];
		}

		if (config('seo.default_title')) {
			return config('seo.default_title');
		}

		return $this->processedJson['name'];
	}

	/**
	 * Returns the Page’s meta description
	 *
	 * @return string
	 */
	public function metaDescription() {
		if (property_exists($this, 'descriptionField') && $this->descriptionField) {
			return strip_tags($this->content[$this->descriptionField]);
		}

		if ($this->_meta['seo'] && $this->_meta['seo']['description']) {
			return $this->_meta['seo']['description'];
		}

		if (config('seo.default_description')) {
			return config('seo.default_description');
		}

		return null;
	}

	/**
	 * Return the Page’s content Collection
	 *
	 * @return mixed
	 */
	public function content() {
		return $this->content;
	}

	/**
	 * Get the Page’s content
	 *
	 * @return string
	 */
	public function slug()
	{
		return $this->processedJson['full_slug'];
	}

	/**
	 * Returns content items from the page’s content-type Block
	 *
	 * @param $name
	 * @return bool|string
	 */
	public function __get($name) {
		try {
			if ($this->content && $this->content->has($name)) {
				return $this->content->{$name};
			}

			return false;
		} catch (Exception $e) {
			return 'Caught exception: ' .  $e->getMessage();
		}
	}
}