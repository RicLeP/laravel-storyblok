<?php


namespace Riclep\Storyblok\Fields;


class StoryLink extends Asset
{
	public function __toString()
	{
		return $this->cached_url;
	}
}