<?php


namespace Riclep\Storyblok\Fields;

/**
 * @property-read string $email
 */
class EmailLink extends Asset
{
	public function __toString(): string
	{
		return $this->email;
	}
}