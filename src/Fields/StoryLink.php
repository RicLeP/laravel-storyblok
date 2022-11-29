<?php


namespace Riclep\Storyblok\Fields;


/**
 * @property-read string $anchor
 * @property-read string $cached_url
 */
class StoryLink extends Asset
{
	public function __toString(): string
	{
		if ($this->anchor) {
			return $this->cached_url . '#' . $this->anchor;
		}

		return $this->cached_url;
	}
}