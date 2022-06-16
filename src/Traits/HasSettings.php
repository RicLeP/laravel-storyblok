<?php

namespace Riclep\Storyblok\Traits;

use Illuminate\Support\Str;

trait HasSettings
{
	protected $_settings;

	public function preprocessHasSettings($content) {
		if (array_key_exists(config('storyblok.settings_field'), $content)) {  // TODO set key in config
			$this->_settings = collect($content[config('storyblok.settings_field')])->keyBy(function($setting) {
				return Str::camel($setting['component']);
			})->map(function ($setting) {
				return collect(array_diff_key($setting, array_flip(['_editable', '_uid', 'component'])));
			});
		}

		// remove the processed item for future items
		unset($content[config('storyblok.settings_field')]);

		return $content;
	}

	public function settings($setting = null) {
		if ($setting) {
			return $this->_settings[$setting];
		}

		return $this->_settings;
	}
}