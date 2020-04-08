<?php


namespace Riclep\Storyblok\Traits;

use League\CommonMark\Converter;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\Ext\Autolink\AutolinkExtension;
use League\CommonMark\Ext\Table\TableExtension;
use League\CommonMark\HtmlRenderer;

trait ConvertsMarkdown
{
	protected $markdown = [];

	private function convertMarkdown() {
		if ($this->markdown && count($this->markdown)) {
			$environment = Environment::createCommonMarkEnvironment();
			$environment->addExtension(new TableExtension());
			$environment->addExtension(new AutolinkExtension());

			$converter = new Converter(new DocParser($environment), new HtmlRenderer($environment));

			foreach ($this->markdown as $markdownField) {
				if ($this->content->has($markdownField)) {
					$this->content[$markdownField . '_html'] = $converter->convertToHtml($this->content[$markdownField]);
				}
			}
		}
	}
}