<?php

namespace Riclep\Storyblok\Traits;

use Illuminate\Support\Str;

trait HasSettings
{
	/**
	 * @var
	 */
	protected $_settings;

	/**
	 * @param $content
	 * @return array
	 */
	public function preprocessHasSettings($content): array
	{
		if (array_key_exists(config('storyblok.settings_field'), $content)) {
			$this->_settings = collect($content[config('storyblok.settings_field')])
				->keyBy(fn($setting) => Str::slug($setting['component'], '_'))
				->map(fn($setting) => collect(array_diff_key($setting, array_flip(['_editable', '_uid', 'component'])))
					->map(function ($setting) {
						if ($this->isCommaSeparatedList($setting)) {
							return $this->isCommaSeparatedList($setting);
						}

						return $setting;
					})
				);
		}

		// remove the processed item for future items
		unset($content[config('storyblok.settings_field')]);

		return $content;
	}

	/**
	 * @param $string
	 * @return array|false|int[]|string[]
	 */
	protected function isCommaSeparatedList($string): array|bool
	{
		if (!preg_match('/^[\w]+(,[\w]*)+$/', $string)) {
			return false;
		}

		return array_map(function($item) {
			$item = trim($item);

			// return $item as a int if it is a string of an int
			if (preg_match('/^\d+$/', $item)) {
				return (int) $item;
			}

			return $item;
		}, explode(',', $string));
	}

	/**
	 * @param $setting
	 * @return mixed
	 */
	public function settings($setting = null): mixed
	{
		if ($setting) {
			return $this->_settings[$setting];
		}

		return $this->_settings;
	}

	/**
	 * @param $setting
	 * @return false|mixed
	 */
	public function hasSetting($setting): mixed
	{
		if ($this->_settings?->has($setting)) {
			return $this->_settings[$setting];
		}

		return false;
	}

	/**
	 * @return false|mixed
	 */
	public function hasSettings(): mixed
	{
		return $this->_settings;
	}
}