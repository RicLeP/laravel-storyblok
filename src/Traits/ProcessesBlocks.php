<?php


namespace Riclep\Storyblok\Traits;


use App\Storyblok\DefaultBlock;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait ProcessesBlocks
{

	private function processBlock($block, $key) {
		if (is_array($block) && !array_key_exists('component', $block)) {
			$block['component'] = $this->getComponentType($block, $key);
		}

		return $this->getBlockType($block, $key) ?: $block;
	}


	private function getBlockType($block, $key) {
		if (is_int($key) && $this->isUuid($block)) {
			$blockClass = $this->getBlockClass($this->component . 'Child'); // TODO check this is okay

			return new $blockClass($this->childStory($block), $key);
		}

		if (is_array($block)) {
			return $this->arrayBlock($block, $key);
		}

		if ($this->isUuid($block) && !in_array($key, ['uuid', 'group_id'])) {
			$blockClass = $this->getBlockClass($this->component . 'Child'); // TODO check this is okay

			return new $blockClass($this->childStory($block), $key);
		}

		return false;
	}

	/**
	 * Works out the component type - this can come from either the component specified in
	 * the response from Storyblok or, in the case of plugins, the plugin name is used
	 */
	private function getComponentType($block, $key) {
		if (array_key_exists('plugin', $block)) {
			return $block['plugin'];
		}

		if (array_key_exists('component', $block) && !is_null($block['component'])) {
			return $block['component'];
		}

		return $key;
	}

	private function arrayBlock($block, $key) {
		$blockClass = $this->getBlockClass($block['component']);

		// or return the default block
		return new $blockClass($block, $key);
	}

	private function getBlockClass($component) {
		if (class_exists(config('storyblok.component_class_namespace') . Str::studly($component) . 'Block')) {
			return config('storyblok.component_class_namespace') . Str::studly($component) . 'Block';
		}

		return config('storyblok.component_class_namespace') . 'DefaultBlock';
	}

	/**
	 * Check if a given string is a valid UUID
	 *
	 * @param   string  $uuid   The string to check
	 * @return  boolean
	 */
	private function isUuid( $uuid ) {
		return !(!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1));
	}
}