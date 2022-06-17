<?php

namespace Riclep\Storyblok\Traits;

use Illuminate\Support\Str;

trait HasSettings
{
	protected $_settings;

	public function preprocessHasSettings($content) {
		if (array_key_exists(config('storyblok.settings_field'), $content)) {  // TODO set key in config
			$this->_settings = collect($content[config('storyblok.settings_field')])->keyBy(function($setting) {
				return Str::slug($setting['component'], '_');
			})->map(function ($setting) {
				$settings = collect(array_diff_key($setting, array_flip(['_editable', '_uid', 'component'])))
					->map(function($setting) {
						if ($this->isComaSeparatedList($setting)) {
							return $this->isComaSeparatedList($setting);
						}

						return $setting;
					});

				return $settings;
			});
		}

		// remove the processed item for future items
		unset($content[config('storyblok.settings_field')]);

		return $content;
	}

	protected function isComaSeparatedList($string) {
		if (!preg_match('/^[\w]+(,[\w]*)+$/', $string)) {
			return false;
		}

		return array_map('trim', explode(',', $string));
	}

	public function settings($setting = null) {
		if ($setting) {
			return $this->_settings[$setting];
		}

		return $this->_settings;
	}

	public function hasSettings() {
		return $this->_settings;
	}
}