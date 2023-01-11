<?php


namespace Riclep\Storyblok\Fields;


use Carbon\Carbon;
use Riclep\Storyblok\Field;

class DateTime extends Field
{
	public function __toString(): string
	{
		if (!$this->content) {
			return '';
		}

		if (property_exists($this, 'format')) {
			return $this->content->format($this->format);
		}

		return config('storyblok.date_format') ? $this->content->format(config('storyblok.date_format')) : $this->content->toDatetimeString();
	}

	/**
	 * Converts the field to a carbon object
	 */
	protected function init(): void
	{
		$this->content = $this->content ? Carbon::parse($this->content) : null;
	}
}