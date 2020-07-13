<?php


namespace Riclep\Storyblok\Fields;


use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\Table\TableExtension;
use Riclep\Storyblok\Field;

class Markdown extends Field
{
	public function __toString()
	{
		$environment = Environment::createCommonMarkEnvironment();
		$environment->addExtension(new TableExtension());

		$converter = new CommonMarkConverter([], $environment);

		return $converter->convertToHtml($this->content);
	}
}