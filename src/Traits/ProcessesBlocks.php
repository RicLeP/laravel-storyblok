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

		$blockType = $this->getBlockType($block, $key);

		if ($blockType) {
			return $blockType;
		}

		return $block ?: false;
	}


	private function getBlockType($block, $key) {
		if (is_int($key) && $this->isUuid($block)) {
			$child = $this->childStory($block);
			$blockClass = $this->getBlockClass($child['content']['component']);

			return new $blockClass($child);
		}

		if (!in_array($key, ['id', 'uuid', 'group_id']) && $this->isUuid($block)) {
			$child = $this->childStory($block);
			$blockClass = $this->getBlockClass($child['content']['component']);

			return new $blockClass($child);
		}

		if (is_array($block)) {
			return $this->arrayBlock($block);
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

	private function arrayBlock($block) {
		$blockClass = $this->getBlockClass($block['component']);

		// or return the default block
		return new $blockClass($block);
	}

	private function getBlockClass($component) {

		//dump($this, $component, '------------------------');

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

	private function processStoryblokKeys($block) {
		$this->_uid = $block['_uid'] ?? null;
		$this->component = $block['component'] ?? null;
		$this->content = collect(array_diff_key($block, array_flip(['_editable', '_uid', 'component', 'plugin'])));
	}
}