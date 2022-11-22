<?php


namespace Riclep\Storyblok\Fields;


class EmailLink extends Asset
{
	public function __toString(): string
	{
		return $this->email;
	}
}