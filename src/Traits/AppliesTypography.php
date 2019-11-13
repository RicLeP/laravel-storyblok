<?php


namespace Riclep\Storyblok\Traits;

use PHP_Typography\PHP_Typography;
use PHP_Typography\Settings as TypographySettings;

trait AppliesTypography
{
	protected $typographySettings = null;

	private function defaultSettings() {
		$settings = new TypographySettings();
		$settings->set_dewidow(true);
		$settings->set_max_dewidow_length(12);
		$settings->set_dewidow_word_number(2);
		$settings->set_hyphenation( false );

		$this->typographySettings = $settings;
	}

	protected function setTypographySettings(TypographySettings $settings) {
		$this->typographySettings = $settings;
	}

	protected function applyTypography() {
		if (!$this->typographySettings) {
			$this->defaultSettings();
		}

		$typography = new PHP_Typography();

		foreach ($this->applyTypography as $field) {
			$this->content[$field] = $typography->process($this->content[$field], $this->typographySettings);
		}
	}
}