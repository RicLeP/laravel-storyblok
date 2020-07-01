<?php


namespace Riclep\Storyblok\Traits;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\Table\TableExtension;

trait ConvertsMarkdown
{
	protected $markdown = [];

	/**
	 * Creates a duplicate to fields in $markdown with an _html suffix
	 * which contain the transformed markdown content as html
	 */
	private function initConvertsMarkdown() {
		if (!empty($this->markdown)) {
			$environment = Environment::createCommonMarkEnvironment();
			$environment->addExtension(new TableExtension());

			$converter = new CommonMarkConverter([], $environment);

			foreach ($this->markdown as $markdownField) {
				if ($this->content->has($markdownField)) {
					$this->content[$markdownField . '_html'] = $converter->convertToHtml($this->content[$markdownField]);
				}
			}
		}
	}
}