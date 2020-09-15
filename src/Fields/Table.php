<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;

class Table extends Field
{
	public function __toString()
	{
		return $this->toHtml($this->content);
	}

	private function toHtml($table) {
		$html = '<table><thead><tr>';

		foreach ($table['thead'] as $header) {
			$html .= '<th>' . $header['value'] . '</th>';
		}

		$html .= '</tr></thead><tbody>';

		foreach ($table['tbody'] as $row) {
			$html .= '<tr>';

			foreach ($row['body'] as $cell) {
				$html .= '<td>' . $cell['value'] . '</td>';
			}

			$html .= '</tr>';
		}

		return $html . '</tbody></table>';
	}
}