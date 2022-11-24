<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;

class Textarea extends Field
{
	/**
	 * Wraps the content in paragraphs when printed
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->autoParagraph($this->content);
	}

	/**
	 * Performs the actual transformation
	 *
	 * @param $text
	 * @return string
	 */
	private function autoParagraph($text): string
	{
		if ($text) {
			$paragraphs = explode("\n", $text);
			return '<p>' . implode('</p><p>', array_filter($paragraphs)) . '</p>';
		}

		return '';
	}
}