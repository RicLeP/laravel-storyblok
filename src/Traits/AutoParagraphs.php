<?php


namespace Riclep\Storyblok\Traits;

trait AutoParagraphs
{
	protected $autoParagraphs = [];

	/**
	 * Loops over every field in $autoParagraphs and creates a duplicate
	 * field suffixed with _html containing html paragraphs
	 */
	private function initAutoParagraphs() {
		if (!empty($this->autoParagraphs)) {
			foreach ($this->autoParagraphs as $field) {
				if ($this->content->has($field)) {
					$this->content[$field . '_html'] = $this->autoParagraph($this->content[$field]);
				}
			}
		}
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