<?php


namespace Riclep\Storyblok\Traits;


use Illuminate\Support\Str;

trait ProcessesBlocks
{
	/**
	 * Starts the process for working out the block’s type
	 *
	 * @param $block
	 * @param $key
	 * @return array|bool|mixed
	 */
	private function processBlock($block, $key) {
		if (is_array($block) && !array_key_exists('component', $block)) {
			$block['component'] = $this->getComponentType($block, $key);
		}

		$blockType = $this->getBlockType($block, $key);

		if ($blockType) {
			return $blockType;
		}

		return $block ?: false;
	}


	/**
	 * Determines if the block is a UUID so needs to be requested via the API
	 * or other special types such as richtext
	 *
	 * @param $block
	 * @param $key
	 * @return bool|mixed
	 */
	private function getBlockType($block, $key) {
		if (is_int($key) && $this->isUuid($block)) {
			$child = $this->childStory($block);
			$blockClass = $this->getBlockClass($child['content']);

			return new $blockClass($child, $this);
		}

		if (!in_array($key, ['id', 'uuid', 'group_id']) && $this->isUuid($block)) {
			$child = $this->childStory($block);
			$blockClass = $this->getBlockClass($child['content']);

			return new $blockClass($child, $this);
		}

		// Richtext
		if (is_array($block) && array_key_exists('type', $block) && $block['type'] === 'doc') {
			return false;
		}

		if (is_array($block)) {
			$blockClass = $this->getBlockClass($block);

			// or return the default block
			return new $blockClass($block, $this);
		}

		return false;
	}

	/**
	 * Works out the component type - this can come from either the
	 * component specified in the response from Storyblok or, in
	 * the case of plugins, the plugin name is used
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

	/**
	 * Determines which class should be used for the current Block or Asset.
	 * Do we have one matching it’s component name?
	 *
	 * @param $block
	 * @return string
	 */
	private function getBlockClass($block) {
		$component = $block['component'];

		if (array_key_exists('fieldtype', $block) && $block['fieldtype'] === 'asset') {
			if (class_exists(config('storyblok.component_class_namespace') . 'Assets\\' . Str::studly($component))) {
				return config('storyblok.component_class_namespace') . 'Assets\\' . Str::studly($component);
			}

			return config('storyblok.component_class_namespace') . 'DefaultAsset';
		}


		if (class_exists(config('storyblok.component_class_namespace') . 'Blocks\\' . Str::studly($component))) {
			return config('storyblok.component_class_namespace') . 'Blocks\\' . Str::studly($component);
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
		return (is_string($uuid) && (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid)));
	}
}