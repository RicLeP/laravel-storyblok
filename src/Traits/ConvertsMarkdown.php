<?php


namespace Riclep\Storyblok\Traits;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\Table\TableExtension;

trait ConvertsMarkdown
{
	protected $markdown = [];

	private function convertMarkdown() {
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