<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;

class Textarea extends Field
{
	public function __toString()
	{
		return $this->autoParagraph($this->content);
	}

	/**
	 * Performs the actual transformation
	 *
	 * @param $text
	 * @return string
	 */
	private function autoParagraph($text) {
		$paragraphs = explode("\n", $text);
		return '<p>' . implode('</p><p>', array_filter($paragraphs)) . '</p>';
	}
}