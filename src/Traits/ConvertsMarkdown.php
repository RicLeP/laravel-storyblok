<?php


namespace Riclep\Storyblok\Traits;

use League\CommonMark\Converter;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use Webuni\CommonMark\TableExtension\TableExtension;

trait ConvertsMarkdown
{
	protected $markdown = [];

	private function convertMarkdown() {
		$environment = Environment::createCommonMarkEnvironment();
	//	$environment->addExtension(new TableExtension());

		$converter = new Converter(new DocParser($environment), new HtmlRenderer($environment));

		if ($this->markdown && count($this->markdown)) {
			foreach ($this->markdown as $markdown) {
				if ($this->content->has($markdown)) {
					$this->content[$markdown . '_html'] = $converter->convertToHtml($this->content[$markdown]);
				}
			}
		}
	}
}