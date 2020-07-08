<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;
use Storyblok\RichtextRender\Resolver;

class RichText extends Field
{
	public function __toString()
	{
		$richtextResolver = new Resolver();
		return $richtextResolver->render($this->content);
	}
}