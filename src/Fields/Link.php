<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;

/**
 * @property-read string $cached_url
 */
class Link extends Field
{
	public function __toString(): string
	{
		return $this->cached_url;
	}
}