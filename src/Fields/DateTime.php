<?php


namespace Riclep\Storyblok\Fields;


use Carbon\Carbon;
use Riclep\Storyblok\Field;

class DateTime extends Field
{
	public function __toString()
	{
		return $this->content->toDatetimeString();
	}

	protected function init() {
		$this->content = Carbon::parse($this->content);
	}
}