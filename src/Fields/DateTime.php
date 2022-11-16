<?php


namespace Riclep\Storyblok\Fields;


use Carbon\Carbon;
use Riclep\Storyblok\Field;

class DateTime extends Field
{
	public function __toString(): string
	{
		return $this->content->toDatetimeString();
	}

	/**
	 * Converts the field to a carbon object
	 */
	protected function init(): void
	{
		$this->content = $this->content ? Carbon::parse($this->content) : null;
	}
}