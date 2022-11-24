<?php


namespace Riclep\Storyblok\Fields;


use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Riclep\Storyblok\Field;

class Markdown extends Field
{
	/**
	 * Converts the markdown to HTML when printed
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		$config = [
			'html_input' => 'escape',
			'allow_unsafe_links' => false,
			'max_nesting_level' => 100,
		];

		$environment = new Environment($config);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new TableExtension());

		$converter = new MarkdownConverter($environment);

		return (string) $converter->convert($this->content);
	}
}