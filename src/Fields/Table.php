<?php


namespace Riclep\Storyblok\Fields;


use Illuminate\Support\Arr;
use Riclep\Storyblok\Field;

class Table extends Field
{
	/**
	 * @var array|string caption for the table
	 */
	protected $caption;

	/**
	 * @var string a class to apply to the <table> tag
	 */
	protected $cssClass;

	/**
	 * @var array|int the column numbers to convert to headers
	 */
	protected $headerColumns;


	public function __toString()
	{
		return $this->toHtml($this->content);
	}

	protected function toHtml($table) {
		$html = '<table ' . ($this->cssClass ? 'class="' . $this->cssClass . '"' : null) . '>';

		if ($this->caption) {
			if (is_array($this->caption)) {
				$html .= '<caption class="' . $this->caption[1] . '">' . $this->caption[0] . '</caption>';
			} else {
				$html .= '<caption>' . $this->caption . '</caption>';
			}
		}

		$html .= '<thead><tr>';

		foreach ($table['thead'] as $header) {
			$html .= '<th>' . nl2br($header['value']) . '</th>';
		}

		$html .= '</tr></thead><tbody>';

		foreach ($table['tbody'] as $row) {
			$html .= '<tr>';

			foreach ($row['body'] as $column => $cell) {
				if ($this->headerColumns && in_array(($column + 1), Arr::wrap($this->headerColumns))) {
					$html .= '<th>' . nl2br($cell['value'])  . '</th>';
				} else {
					$html .= '<td>' . nl2br($cell['value'])  . '</td>';
				}
			}

			$html .= '</tr>';
		}

		return $html . '</tbody></table>';
	}

	public function caption($caption) {
		$this->caption = $caption;

		return $this;
	}

	public function cssClass($cssClass) {
		$this->cssClass = $cssClass;

		return $this;
	}
}