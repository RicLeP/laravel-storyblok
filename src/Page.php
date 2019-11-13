<?php


namespace Riclep\Storyblok;

use Riclep\Storyblok\Traits\ProcessesBlocks;

abstract class Page
{
	use ProcessesBlocks;

	private $processedJson;
	private $content;
	protected $usePageTitle;
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
		}

		return $this;
	}

	/**
	 * Processes the page’s meta content
	 */
	public function process() {
		$this->seo = array_key_exists('seo', $this->processedJson) ? $this->processedJson['seo'] : null;

		return $this;
	}

	public function getBlocks() {
		$this->content = $this->processBlock($this->processedJson['content'], 'root');

		return $this;
	}

	/**
	 * @return array
	 */
	protected function view() {
		$viewFile = strtolower(subStr(basename(get_class($this)), 0, -4));

		if ($viewFile !== 'default') {
			$views[] = config('storyblok.view_path') . 'pages.' . $viewFile;
		}

		$views[] = config('storyblok.view_path') . 'pages.default';

		return $views;
	}

	/**
	 * Reads the story
	 *
	 * @return array
	 */
	public function render() {
		return view()->first(
			$this->view(),
			[
				'title' => $this->title(),
				'meta_description' => $this->metaDescription(),
				'content' => $this->content(),
			]
		);
	}

	public function title() {

		if ($this->seo) {
			return $this->seo['title'];
		}

		if ($this->usePageTitle) {
			return $this->title;
		}

		return config('seo.default_title');
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
}