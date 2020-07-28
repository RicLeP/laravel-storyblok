<?php


namespace Riclep\Storyblok\Fields;


class EmailLink extends Asset
{
	public function __toString()
	{
		return $this->email;
	}
}